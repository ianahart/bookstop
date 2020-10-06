const removeBookBtn = document.querySelector('.remove-button');
const cancelButton = document.querySelector('.no');
const modal = document.querySelector('.modal');

const openModal = () => {
  modal.classList.remove('hide-modal');
};

const closeModal = () => {
  modal.classList.add('hide-modal');
};

if (removeBookBtn) {
  removeBookBtn.addEventListener('click', openModal);
}

if (cancelButton) {
  cancelButton.addEventListener('click', closeModal);
}
