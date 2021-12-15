export const Blessing = ($ => {
  const _button = document.getElementById('js-choose-blessing');
  const _field = document.querySelector('input[name="billing_another_person_blessing"]');
  const _popup = document.getElementById('blessing-popup');
  const _categorySelect = document.getElementById('js-blessing-category');
  const _categoryMessageSelect = document.getElementById('js-blessing-message');
  let _blessings = [];

  function _bindEvents() {
    if (_button) {
      _button.addEventListener('click', e => {
        e.preventDefault();
        openPopup();
      });
    }

    if (_popup) {
      _popup.addEventListener('click', e => {
        const closeButton = e.target.closest('.js-close');

        if (!closeButton) return;
        e.preventDefault();

        closePopup();
      });
    }


    if (_categorySelect) {
      _categorySelect.addEventListener('change', _handleCategoryChange);
    }
    if (_categoryMessageSelect) {
      _categoryMessageSelect.addEventListener('change', _handleCategoryMessageChange);
    }
  }

  function openPopup() {
    _popup.classList.add('is-active');
  }

  function closePopup() {
    _popup.classList.remove('is-active');
  }

  async function _handleCategoryChange(e) {
    if (!_blessings.length) {
      const body = new FormData();
      body.append('action', 'get_option');
      body.append('name', 'another_person_blessing');

      const resp = await fetch(wpdata.ajaxUrl, {
        method: 'POST',
        body
      });

      const data = await resp.json();
      _blessings = data;
    }

    const blessing = _blessings.find(item => {
      return item?.categoryName === e.target.value;
    });

    const fragment = document.createDocumentFragment();

    if (!blessing.items.length) {
      const option = document.createElement('option');
      option.value = '';
      option.text = 'No items';
      option.disabled = true;
      option.selected = true;
      fragment.append(option);
    } else {
      const firstOption = document.createElement('option');
      firstOption.value = '';
      firstOption.text = 'Choose message';

      fragment.append(firstOption);

      blessing.items.forEach(item => {
        const option = document.createElement('option');
        option.value = item;
        option.text = item;
        fragment.append(option);
      });
    }

    _categoryMessageSelect.innerHTML = '';
    _categoryMessageSelect.append(fragment);
  }

  function _handleCategoryMessageChange(e) {
    _field.value = e.target.value;
    closePopup();
  }

  return {
    init() {
      _bindEvents();

      $(document.body).on('sp_layout_change_finished', (e, data) => {
        $('input[name="billing_another_person_blessing"]').val('');

        // .js-pickup-blessing
        $('.js-pickup-blessing #js-choose-blessing').on('click', e => {
          e.preventDefault();
          $('.js-pickup-blessing #blessing-popup').addClass('is-active');
        });

        $('.js-pickup-blessing #blessing-popup').on('click', '.js-close', e => {
          e.preventDefault();
          $('.js-pickup-blessing #blessing-popup').removeClass('is-active');
        });

        $('.js-pickup-blessing #js-blessing-category').on('change', async e => {
          if (!_blessings.length) {
            const body = new FormData();
            body.append('action', 'get_option');
            body.append('name', 'another_person_blessing');

            const resp = await fetch(wpdata.ajaxUrl, {
              method: 'POST',
              body
            });

            const data = await resp.json();
            _blessings = data;
          }

          const blessing = _blessings.find(item => {
            return item?.categoryName === e.target.value;
          });

          const fragment = document.createDocumentFragment();

          if (!blessing.items.length) {
            const option = document.createElement('option');
            option.value = '';
            option.text = 'No items';
            option.disabled = true;
            option.selected = true;
            fragment.append(option);
          } else {
            const firstOption = document.createElement('option');
            firstOption.value = '';
            firstOption.text = 'Choose message';

            fragment.append(firstOption);

            blessing.items.forEach(item => {
              const option = document.createElement('option');
              option.value = item;
              option.text = item;
              fragment.append(option);
            });
          }

          $('.js-pickup-blessing #js-blessing-message')[0].innerHTML = ''
          $('.js-pickup-blessing #js-blessing-message')[0].append(fragment);
        });

        $('.js-pickup-blessing #js-blessing-message').on('change', e => {
          $('input[name="billing_another_person_blessing"]').val(e.target.value);
          $('.js-pickup-blessing #blessing-popup').removeClass('is-active');
        });

      });
    }
  }
})(jQuery)