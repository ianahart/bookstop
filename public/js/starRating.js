const starRatingContainer = document.querySelectorAll('.star-rating-default');
const stars = document.querySelectorAll('.star');

let currentUser;

if (starRatingContainer.length) {
  currentUser = starRatingContainer[0].getAttribute('data-currentUser');
}

let rating = 0;

const temp = Array.from(starRatingContainer);

temp.forEach((container) => {
  const count = container.getAttribute('data-review');

  Array.from(container.children).forEach((star, index) => {
    if (index <= count - 1) {
      star.classList.remove('star-unchecked');
    } else {
      star.classList.add('star-unchecked');
    }
  });
});

const submitRating = (rating, id) => {
  window.location.replace(
    window.location.href + `&rating=${rating}&userId=${id}`
  );
};

const highlight = (e) => {
  if (e.target.classList.contains('star-unchecked')) {
    rating++;
  }
  e.target.classList.remove('star-unchecked');
};

const resetStars = () => {
  const temp = Array.prototype.slice.call(stars);
  const currentOwnerStars = temp.filter((star) => {
    return star.getAttribute('data-userId') === currentUser;
  });

  currentOwnerStars.forEach((star) => {
    star.classList.add('star-unchecked');
  });
  rating = 0;
};

stars.forEach((star) => {
  if (currentUser === star.getAttribute('data-userid')) {
    star.addEventListener('mouseover', highlight);
    star.addEventListener('click', (e) => {
      submitted = true;
      star.parentElement.removeEventListener('mouseleave', resetStars);
      if (
        !window.location.search.includes('rating') &&
        star.parentElement.getAttribute('data-review') === '0'
      ) {
        submitRating(rating, currentUser);
      }
    });
  }
});

if (window.location.search.includes('rating')) {
  stars.forEach((star) => {
    star.removeEventListener('mouseover', highlight);
  });
}

starRatingContainer.forEach((container) => {
  if (container.getAttribute('data-review') !== '0') {
    const children = Array.prototype.slice.call(container.children);
    children.forEach((childNode) => {
      childNode.removeEventListener('mouseover', highlight);
    });
  }

  if (container.getAttribute('data-owner') !== currentUser) {
    container.removeEventListener('mouseleave', resetStars);
  } else if (
    container.getAttribute('data-owner') === currentUser &&
    container.getAttribute('data-review') === '0'
  ) {
    container.addEventListener('mouseleave', resetStars);
  }
});
