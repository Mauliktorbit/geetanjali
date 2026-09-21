/**
 * Admin image preview — runs on its own so a product/collection/category
 * photo appears in the Image preview panel as soon as a file is chosen.
 */
(function () {
  'use strict';

  function isImageFile(file) {
    if (!file) {
      return false;
    }
    if (file.type && file.type.indexOf('image/') === 0) {
      return true;
    }
    return /\.(jpe?g|png|webp|gif|bmp|svg)$/i.test(file.name || '');
  }

  function showPhoto(container, src) {
    if (!container || !src) {
      return;
    }

    var img = container.querySelector('[data-preview-image], img.category-form__photo, img[data-main-preview-img], img');
    var placeholder = container.querySelector('[data-preview-placeholder], .category-form__placeholder');
    var label = container.querySelector('.label');
    var clearBtn = container.querySelector('[data-clear-preview]');

    if (!img) {
      img = document.createElement('img');
      img.setAttribute('data-preview-image', '');
      img.className = 'category-form__photo';
      img.alt = 'Image preview';
      if (placeholder) {
        container.insertBefore(img, placeholder);
      } else {
        container.appendChild(img);
      }
    }

    img.onload = function () {
      img.removeAttribute('hidden');
      img.style.display = 'block';
      if (placeholder) {
        placeholder.hidden = true;
        placeholder.style.display = 'none';
      }
    };
    img.onerror = function () {
      if (placeholder) {
        placeholder.hidden = false;
        placeholder.style.display = '';
      }
    };

    container.classList.add('is-previewing');
    img.removeAttribute('hidden');
    img.style.setProperty('display', 'block', 'important');
    img.src = src;

    if (placeholder) {
      placeholder.hidden = true;
      placeholder.style.setProperty('display', 'none', 'important');
    }
    if (label) {
      label.textContent = 'Image preview';
    }
    if (clearBtn) {
      clearBtn.hidden = false;
    }
    container.hidden = false;
    container.style.display = '';
  }

  function previewFile(file, container) {
    if (!isImageFile(file) || !container) {
      return;
    }

    if (window.URL && typeof URL.createObjectURL === 'function') {
      try {
        showPhoto(container, URL.createObjectURL(file));
      } catch (err) {}
    }

    var reader = new FileReader();
    reader.onload = function (event) {
      showPhoto(container, event.target.result);
    };
    reader.readAsDataURL(file);
  }

  function targetFor(input) {
    var selector = input.getAttribute('data-preview-target');
    if (selector) {
      return document.querySelector(selector);
    }
    if (input.hasAttribute('data-main-image-input')) {
      return document.querySelector('[data-main-preview], #product-image-preview');
    }
    var form = input.form || input.closest('form');
    if (form) {
      return form.querySelector('[data-image-preview]');
    }
    return input.parentElement;
  }

  function previewGallery(input) {
    var box = document.querySelector('[data-gallery-previews]');
    if (!box) {
      return;
    }

    box.querySelectorAll('[data-gallery-item="pending"]').forEach(function (node) {
      node.remove();
    });

    Array.prototype.forEach.call(input.files || [], function (file) {
      if (!isImageFile(file)) {
        return;
      }
      var item = document.createElement('div');
      item.className = 'gallery-preview-item';
      item.setAttribute('data-gallery-item', 'pending');
      var img = document.createElement('img');
      img.alt = file.name || 'Photo';
      img.src = (window.URL && URL.createObjectURL) ? URL.createObjectURL(file) : '';
      if (!img.src) {
        var reader = new FileReader();
        reader.onload = function (event) {
          img.src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
      item.appendChild(img);
      box.appendChild(item);
    });
  }

  function onFileChange(input) {
    if (!input || input.type !== 'file' || input.hasAttribute('data-no-preview')) {
      return;
    }

    var file = input.files && input.files[0];
    if (!file) {
      return;
    }

    if (input.hasAttribute('data-gallery-input')) {
      previewGallery(input);
      return;
    }

    previewFile(file, targetFor(input));

    var removeField = input.form && (
      input.form.querySelector('[data-remove-main-image]') ||
      input.form.querySelector('[data-remove-image]')
    );
    if (removeField) {
      removeField.value = '0';
    }
  }

  document.addEventListener('change', function (event) {
    var input = event.target;
    if (input && input.matches && input.matches('input[type="file"]')) {
      onFileChange(input);
    }
  }, true);

  window.previewAdminImage = onFileChange;
})();
