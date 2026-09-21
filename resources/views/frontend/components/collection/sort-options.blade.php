@props(['sort' => 'popularity'])

<option value="popularity" @selected($sort === 'popularity')>Popularity</option>
<option value="newest" @selected($sort === 'newest')>Newest</option>
<option value="price_low" @selected($sort === 'price_low')>Price Low to High</option>
<option value="price_high" @selected($sort === 'price_high')>Price High to Low</option>
<option value="rating" @selected($sort === 'rating')>Best Rated</option>
