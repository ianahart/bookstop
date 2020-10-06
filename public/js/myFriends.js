const messageBtns = document.querySelectorAll('#message-btn');
const messages = document.querySelectorAll('div[name="message"]');
const userNames = document.querySelectorAll('.friend-username');
const filterInput = document.querySelector('input[name="filter"]');
const sendBtns = document.querySelectorAll('button[name="sendmessage"]');

function handlePaste(e) {
  e.preventDefault();
  const text = e.clipboardData.getData('text/plain');
  document.execCommand('insertHTML', false, text);
}

const displayModal = (e) => {
  if (e.target.tagName === 'BUTTON') {
    const modal = e.target.previousElementSibling.previousElementSibling;
    modal.classList.remove('hidden');
    modal.classList.add('message-modal');
  }
};

const getUserCurrentTime = (date) => {
  let period = 'am';
  let hours = date.getHours();
  let minutes = date.getMinutes();

  if (hours >= 12) {
    period = 'pm';
    hours = hours - 12;
  }

  if (minutes < 10) {
    minutes = `0${minutes}`;
  }
  const time = `${hours}:${minutes}${period}`;

  const cookieVal = time;
  document.cookie = 'cur_time=' + cookieVal;
};

getUserCurrentTime(new Date());

const filterUserNames = (e) => {
  const filter = e.target.value.toLowerCase();
  userNames.forEach((userName) => {
    const userNameText = userName.textContent.toLowerCase();
    if (userNameText.includes(filter)) {
      userName.parentElement.classList.remove('hidden');
    } else {
      userName.parentElement.classList.add('hidden');
    }
  });
};

sendBtns.forEach((sendBtn) => {
  sendBtn.addEventListener('click', (e) => {
    if (e.target.tagName !== 'BUTTON') {
      e.preventDefault();
    } else {
      const hiddenInput = e.target.parentElement.previousElementSibling;
      const editableDiv =
        e.target.parentElement.previousElementSibling.previousElementSibling
          .previousElementSibling;
      hiddenInput.value = editableDiv.innerHTML;
    }
  });
});

messages.forEach((message) => {
  message.addEventListener('keypress', (e) => {
    if (e.keyCode === 13) {
      const br = document.createElement('br');
      message.appendChild(br);
    }
  });
});

messages.forEach((message) => {
  message.addEventListener('paste', handlePaste);
});

messageBtns.forEach((messageBtn) => {
  messageBtn.addEventListener('click', displayModal);
});

if (filterInput) {
  filterInput.addEventListener('keyup', filterUserNames);
}
