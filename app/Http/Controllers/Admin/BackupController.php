<?php

namespace App\Http\Controllers\Admin;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends AdminController
{
    public function index()
    {
        $items = Backup::with('creator')->latest()->paginate(20);

        return view('admin.backups.index', compact('items'));
    }

    public function create(Request $request)
    {
        $type = $request->input('type', 'database');
        $disk = Storage::disk('local');
        $dir = 'backups';
        $disk->makeDirectory($dir);

        $filename = 'backup-' . $type . '-' . now()->format('Ymd-His') . '.sql';
        $path = $dir . '/' . $filename;

        if ($type === 'database' || $type === 'full') {
            $this->dumpDatabase($disk->path($path));
        } else {
            $disk->put($path, "-- Files backup placeholder\n");
        }

        $size = $disk->exists($path) ? $disk->size($path) : 0;

        Backup::create([
            'filename' => $filename,
            'path' => $path,
            'size' => $size,
            'type' => $type,
            'created_by' => Auth::id(),
        ]);

        return $this->success('Backup created.');
    }

    public function download(Backup $backup)
    {
        if (! Storage::disk('local')->exists($backup->path)) {
            return $this->error('Backup file missing.');
        }

        return Storage::disk('local')->download($backup->path, $backup->filename);
    }

    public function restore(Backup $backup)
    {
        if (! Storage::disk('local')->exists($backup->path)) {
            return $this->error('Backup file missing.');
        }

        $sql = Storage::disk('local')->get($backup->path);
        if (! str_contains($sql, '--')) {
            return $this->error('Invalid backup file.');
        }

        DB::unprepared($sql);

        return $this->success('Backup restore executed.');
    }

    public function destroy(Backup $backup)
    {
        if (Storage::disk('local')->exists($backup->path)) {
            Storage::disk('local')->delete($backup->path);
        }
        $backup->delete();

        return $this->success('Backup deleted.');
    }

    protected function dumpDatabase(string $absolutePath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $config['database'];
        $sql = "-- Backup generated " . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $name = $table->$key;
            $create = DB::select("SHOW CREATE TABLE `{$name}`");
            $createSql = $create[0]->{'Create Table'} ?? null;
            if (! $createSql) {
                continue;
            }

            $sql .= "DROP TABLE IF EXISTS `{$name}`;\n{$createSql};\n\n";
            $rows = DB::table($name)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($v) {
                    if ($v === null) {
                        return 'NULL';
                    }

                    return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $v) . "'";
                }, (array) $row);
                $sql .= "INSERT INTO `{$name}` VALUES (" . implode(',', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($absolutePath, $sql);
    }
}
