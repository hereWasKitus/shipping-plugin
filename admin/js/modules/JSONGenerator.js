const JSONGenerator = (() => {
  const form = document.querySelector('.json-generator-form');
  const link = document.querySelector('.js-download-json');
  const errorEl = document.querySelector('.json-generator-error');

  function bindEvents() {
    form?.addEventListener('submit', handleSubmit);
  }

  async function handleSubmit(e) {
    e.preventDefault();
    errorEl.textContent = '';

    const body = new FormData(e.target);
    body.set('action', 'generate_json');

    const response = await fetch(wpdata.ajaxUrl, {
      method: 'POST',
      body
    });

    const data = await response.json();

    if ( !data['success'] ) {
      errorEl.textContent = data['error'];
      return;
    }

    link.href = data['file'];
    link.click();
  }

  return {
    init () {
      bindEvents();
    }
  }
})();

JSONGenerator.init();