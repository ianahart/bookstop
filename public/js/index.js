const links = document.querySelectorAll('.link');
const account = document.querySelector('.account');
const hamburger = document.querySelector('.hamburger');
const mainContent = document.querySelector('.content');
const accountBtn = document.querySelector('.account-btn');
const deletePopup = document.querySelector('.delete-popup');
const navigationWrapper = document.querySelector('#nav-wrapper');
const deletePopUpBtn = document.querySelector('.cancel-delete-popup');
const deleteAccountBtn = document.querySelector('.delete-account-btn');

let mobileMenuOpen = false;

const closeMenu = () => {
  navigationWrapper.classList.add('show-navigation');
  mobileMenuOpen = false;
};

const toggleNavMenu = () => {
  if (navigationWrapper.classList.contains('show-navigation')) {
    navigationWrapper.classList.remove('show-navigation');
    mobileMenuOpen = true;
  } else {
    closeMenu();
  }
};

const showAccount = () => {
  account.classList.remove('hidden');
};

const checkAllowedClasses = (e, array) => {
  const classList = [...e.target.classList];

  const classFound = classList.some((val) => array.indexOf(val) !== -1);

  return classFound;
};

const hideAccount = (e) => {
  const classFound = checkAllowedClasses(e, [
    'account',
    'account-btn',
    'delete-account-btn',
    'cancel-delete-popup',
  ]);
  if (!classFound) {
    account.classList.add('hidden');
    deletePopup.classList.add('hidden');
  }
};

const hideDeletePopup = () => {
  deletePopup.classList.add('hidden');
};

const showDeletePopup = () => {
  deletePopup.classList.remove('hidden');
};

deleteAccountBtn.addEventListener('click', showDeletePopup);

deletePopUpBtn.addEventListener('click', hideDeletePopup);

document.body.addEventListener('click', (e) => {
  const classFound = checkAllowedClasses(e, [
    'hamburger',
    'account-btn',
    'delete-account-btn',
    'cancel-delete-popup',
  ]);
  if (!classFound) {
    closeMenu();
  }
});

links.forEach((link) => {
  link.addEventListener('click', closeMenu);
});

hamburger.addEventListener('click', toggleNavMenu);

if (accountBtn !== null) {
  accountBtn.addEventListener('click', showAccount);
}

document.body.addEventListener('click', hideAccount);
