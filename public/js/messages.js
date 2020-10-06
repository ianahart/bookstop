const messagePreviews = document.querySelectorAll('.message-preview');
const innerMessageDivs = document.querySelectorAll('.inner-message');
const editableDivs = document.querySelectorAll('.editable-div');
const sendBtns = document.querySelectorAll('button[name="reply"]');
const replyTriggers = document.querySelectorAll('#reply-trigger');
const goBackButton = document.querySelectorAll('#go-back-btn');

const showInnerMessage = (e) => {
  const hiddenDiv =
    e.target.nextElementSibling.parentElement.nextElementSibling;

  hiddenDiv.classList.remove('hidden');
};

const markAsRead = () => {
  messagePreviews.forEach((div) => {
    if (div.getAttribute('data-mark_as_read') === 'true') {
      div.classList.add('marked-as-read');
    }
  });
};

markAsRead();

const hideInnerMessage = (e) => {
  innerMessageDivs.forEach((div) => {
    div.classList.add('hidden');
  });
};

const handlePaste = (e) => {
  e.preventDefault();
  const text = e.clipboardData.getData('text/plain');
  document.execCommand('insertHTML', false, text);
};

const showReplyForm = (e) => {
  e.preventDefault();
  let hiddenReplyForm;
  if (e.target.tagName === 'BUTTON') {
    hiddenReplyForm =
      e.target.parentElement.parentElement.previousElementSibling;
  } else if (e.target.tagName === 'I') {
    hiddenReplyForm =
      e.target.parentElement.parentElement.parentElement.previousElementSibling;
  }
  hiddenReplyForm.classList.remove('reply-hidden');
  hiddenReplyForm.classList.add('reply-form-modal');
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

goBackButton.forEach((button) => {
  button.addEventListener('click', hideInnerMessage);
});

messagePreviews.forEach((div) => {
  div.addEventListener('click', showInnerMessage);
});

replyTriggers.forEach((trigger) => {
  trigger.addEventListener('click', showReplyForm);
});

editableDivs.forEach((editableDiv) => {
  editableDiv.addEventListener('keypress', (e) => {
    if (e.keyCode === 13) {
      const br = document.createElement('br');
      editableDiv.appendChild(br);
    }
  });
});

sendBtns.forEach((sendBtn) => {
  sendBtn.addEventListener('click', (e) => {
    if (e.target.tagName !== 'BUTTON') {
      e.preventDefault();
    } else {
      const hiddenInput = e.target.parentElement.previousElementSibling;
      const div =
        e.target.parentElement.previousElementSibling.previousElementSibling
          .previousElementSibling;

      hiddenInput.value = div.innerHTML;
    }
  });
});

editableDivs.forEach((editableDiv) => {
  editableDiv.addEventListener('paste', handlePaste);
});
