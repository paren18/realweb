document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('.news-grid');

    if (!grid) return;

    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.like-btn');
        if (!btn) return;

        const wrapper = btn.closest('.like-wrapper');
        const newsId = wrapper.dataset.newsId;

        fetch('/ajax/like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'news_id=' + newsId + '&sessid=' + BX.message('bitrix_sessid')
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    wrapper.querySelector('.like-count').textContent = data.count;
                    btn.classList.toggle('liked', data.liked);
                }
            });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('load-more');
    if (!btn) return;

    btn.addEventListener('click', function () {
        const nextPage = btn.dataset.nextPage;
        const baseUrl = btn.dataset.url;
        const fullUrl = baseUrl + '?PAGEN_1=' + nextPage;

        const spinner = document.createElement('div');
        spinner.innerText = 'Загрузка...';
        btn.replaceWith(spinner);

        fetch(fullUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newItems = doc.querySelectorAll('.news-card');

                const container = document.querySelector('.news-grid');
                newItems.forEach(item => container.appendChild(item));

                // проверить, есть ли ещё страницы
                const nextBtn = doc.querySelector('#load-more');
                if (nextBtn) {
                    const newBtn = nextBtn.cloneNode(true);
                    spinner.replaceWith(newBtn);

                    // снова вешаем обработчик на новый
                    newBtn.addEventListener('click', arguments.callee);
                } else {
                    spinner.remove();
                }
            });
    });
});
