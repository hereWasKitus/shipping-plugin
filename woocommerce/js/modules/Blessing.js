export const Blessing = ($ => {
  const _blessingCategorySelector = '.js-blessing-category';
  const _blessingPopupSelector = '.blessing-popup';
  let _blessings = [];

  /**
   * Create html for blessing popup
   * @param {Array<String>} items
   */
  const createBlessingHTML = ( items ) => {
    let html = '';

    items.forEach( item => {
      html += `
      <li>
        <p>${item}</p>
        <button>בחר ברכה</button>
      </li>
      `
    } );

    return html;
  }

  /**
   * Fills popup blessing list with html of selected category's items
   * @param {String} category category of the items
   */
  const fillPopup = async category => {
    if ( !_blessings.length ) {
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

    let blessings = _blessings.filter( obj => obj.categoryName === category )[0].items;
    const blessingHTML = createBlessingHTML( blessings );

    $(`${_blessingPopupSelector} ul`).html( blessingHTML );
  }

  /**
   * Handle category select change
   */
  const handleChange = async e => {
    const { value } = e.target;
    await fillPopup( value );
    $(_blessingPopupSelector).addClass('is-active');
  }

  /**
   * Handle blessing item select
   */
  const handleBlessingSelect = e => {
    e.preventDefault();
    const text = e.currentTarget.parentElement.querySelector('p').textContent;
    $('[name="billing_another_person_blessing"]').text( text );
    $(_blessingPopupSelector).removeClass('is-active');
  }

  return {
    init () {
      $(document).on('change', _blessingCategorySelector, handleChange);
      $(document).on('click', `${_blessingPopupSelector} .blessing-popup-list button`, handleBlessingSelect);
      $(document).on('click', `${_blessingPopupSelector} [data-modal-close]`, e => {
        e.preventDefault();
        $(_blessingPopupSelector).removeClass('is-active');
      });
      $(document).on('click', _blessingPopupSelector, e => {
        if ( e.target.classList.contains('blessing-popup') ) {
          $(_blessingPopupSelector).removeClass('is-active');
        }
      });
    }
  }
})(jQuery)