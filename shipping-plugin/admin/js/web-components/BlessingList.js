export class BlessingList extends HTMLElement {
  connectedCallback() {
    const shadow = this.attachShadow({ mode: 'open' });
    const items = this.getAttribute('items')?.split('|') || [];

    const wrapperEl = document.createElement('div');
    wrapperEl.classList.add('blessing');

    const titleEl = document.createElement('h3');
    titleEl.classList.add('blessing-category');

    const titleElInput = document.createElement('input');
    titleElInput.type = 'text';
    titleElInput.placeholder = 'Enter category name';
    titleElInput.value = this.getAttribute('category') || '';

    const removeCategoryButton = document.createElement('button');
    removeCategoryButton.textContent = "Remove category";
    removeCategoryButton.id = 'js-remove-blessing';
    removeCategoryButton.classList.add('button');

    titleEl.append(titleElInput, removeCategoryButton);

    const listEl = document.createElement('ul');
    listEl.classList.add('blessing-items');

    if (items.length) {
      items.forEach(text => {
        const listItemEl = this._createListItem(text);
        listEl.append(listItemEl);
      });
    } else {
      const listItemEl = this._createListItem('');
      listEl.append(listItemEl);
    }

    const addItemButtonEl = document.createElement('button');
    addItemButtonEl.classList.add('button');
    addItemButtonEl.id = 'js-add-list-item';
    addItemButtonEl.textContent = 'Add';

    const style = document.createElement('style');
    style.textContent = `
      .blessing {
        padding: 8px;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.19);
        background-color: #fff;
        border-radius: 4px;
      }

      .blessing .button {
        background-color: #2271b1;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        line-height: 2;
        padding: 0 10px;
        color: #fff;
      }

      .blessing-category {
        margin: 0 0 20px;
      }

      .blessing input {
        padding: 5px 8px;
        font-size: 16px;
        margin-right: 10px;
      }

      .blessing-items {
        list-style-type: none;
        margin: 0 0 20px;
        padding-left: 0;
      }

      .blessing-items .blessing-item:not(:last-child) {
        margin-bottom: 10px;
      }

      .blessing-item input {
        font-size: 14px;
      }
    `;

    wrapperEl.append(titleEl, listEl, addItemButtonEl);
    shadow.append(style, wrapperEl);
    this._bindEvents();
  }

  _bindEvents() {
    this.shadowRoot.getElementById('js-add-list-item').addEventListener('click', e => {
      e.preventDefault();
      return this._addListItem();
    });

    this.shadowRoot.querySelector('.blessing-items').addEventListener('click', e => {
      e.preventDefault();
      const removeItemButton = e.target.closest('.js-remove-item');
      const nodeToRemove = e.target.closest('.blessing-item');

      if (!removeItemButton) return;

      nodeToRemove.remove();
    });

    this.shadowRoot.getElementById('js-remove-blessing').addEventListener('click', () => this.remove());
  }

  _addListItem() {
    this.shadowRoot.querySelector('.blessing-items').append(this._createListItem());
  }

  _createListItem(text = '') {
    const listItemEl = document.createElement('li');
    listItemEl.classList.add('blessing-item');

    const listItemInputEl = document.createElement('input');
    listItemInputEl.type = 'text';
    listItemInputEl.placeholder = 'Enter your message';
    listItemInputEl.value = text;

    const listItemRemoveEl = document.createElement('button');
    listItemRemoveEl.textContent = 'Remove';
    listItemRemoveEl.classList.add('js-remove-item', 'button');

    listItemEl.append(listItemInputEl, listItemRemoveEl);
    return listItemEl;
  }

  getBlessings() {
    const blessingItems = this.shadowRoot.querySelectorAll('.blessing-items input');

    return {
      categoryName: this.shadowRoot.querySelector('.blessing-category input').value,
      items: [...blessingItems].map(input => input.value)
    };
  }
}