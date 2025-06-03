// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Get elements
  const navLinks = document.querySelectorAll('nav a');
  const rentNowButtons = document.querySelectorAll('.car-card button');
  const notificationBtn = document.getElementById('notificationBtn');
  const notificationBar = document.getElementById('notificationBar');
  const searchBar = document.getElementById('searchBar');
  
  // Add active class to current page link
  navLinks.forEach(link => {
      if (link.getAttribute('href') === location.pathname.split('/').pop()) {
          link.classList.add('active');
      } else {
          link.classList.remove('active');
      }
  });
  
  // Add click event to Rent Now buttons
    if (rentNowButtons) {
        rentNowButtons.forEach(button => {
            button.addEventListener('click', function() {
                window.location.href = 'order.html';
            });
        });
    }
  // Toggle notification bar on button click
  if (notificationBtn && notificationBar) {
      let isNotificationOpen = false;
      
      notificationBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          isNotificationOpen = !isNotificationOpen;
          
          if (isNotificationOpen) {
              // Show notification bar
              notificationBar.classList.add('active');
          } else {
              // Hide notification bar
              notificationBar.classList.remove('active');
          }
      });
      
      // Close notification bar when clicking outside
      document.addEventListener('click', function(e) {
          if (isNotificationOpen && !notificationBar.contains(e.target) && e.target !== notificationBtn) {
              notificationBar.classList.remove('active');
              isNotificationOpen = false;
          }
      });
  }
  
  // Search functionality
  if (searchBar) {
      searchBar.addEventListener('keyup', function() {
          const searchTerm = this.value.toLowerCase();
          const carCards = document.querySelectorAll('.car-card');
          
          carCards.forEach(card => {
              const carName = card.querySelector('h3').textContent.toLowerCase();
              if (carName.includes(searchTerm)) {
                  card.style.display = 'block';
              } else {
                  card.style.display = 'none';
              }
          });
      });
  }
});


















