const filterInput = document.querySelector('.filter-input');
const bookTitles = document.querySelectorAll('.book-title');

const filterBooks = (e) => {
  let filter = e.target.value.toLowerCase();

  bookTitles.forEach((title) => {
    const author = title.nextElementSibling.nextElementSibling;

    const titleText = title.textContent.toLowerCase();

    const authorText = author.textContent.toLowerCase();

    if (!titleText.includes(filter) && authorText.includes(filter)) {
      title.parentElement.classList.remove('hidden');
    } else if (titleText.includes(filter) && !authorText.includes(filter)) {
      title.parentElement.classList.remove('hidden');
    } else if (!titleText.includes(filter) && !authorText.includes(filter)) {
      title.parentElement.classList.add('hidden');
    } else {
      title.parentElement.classList.remove('hidden');
    }
  });
};

filterInput.addEventListener('keyup', filterBooks);
