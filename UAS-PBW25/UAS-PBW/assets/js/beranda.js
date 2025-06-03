// Khusus untuk menangani notifikasi pada halaman beranda
document.addEventListener('DOMContentLoaded', function() {
  // Get elements
  const notificationBtn = document.getElementById('notificationBtn');
  const notificationBar = document.getElementById('notificationBar');
  
  if (notificationBtn && notificationBar) {
      // Toggle notification bar when clicking the notification button
      notificationBtn.addEventListener('click', function(e) {
          e.stopPropagation();
          notificationBar.classList.toggle('active');
      });
      
      // Close notification bar when clicking outside
      document.addEventListener('click', function(e) {
          if (!notificationBtn.contains(e.target) && !notificationBar.contains(e.target)) {
              notificationBar.classList.remove('active');
          }
      });
  }

  // Show and hide login modal
  const loginBtn = document.getElementById('loginBtn');
  const loginModal = document.getElementById('loginModal');
  const closeModal = document.getElementById('closeModal');

  loginBtn.addEventListener('click', () => {
      loginModal.classList.remove('hidden');
  });

  closeModal.addEventListener('click', () => {
      loginModal.classList.add('hidden');
  });
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle notification dropdown
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (notificationBtn && notificationDropdown) {
        // Toggle notification dropdown
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden'); // Tampilkan atau sembunyikan dropdown
        });

        // Close notification dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden'); // Sembunyikan dropdown
            }
        });
    }

    // Show and hide login modal
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const closeModal = document.getElementById('closeModal');

    if (loginBtn && loginModal && closeModal) {
        loginBtn.addEventListener('click', () => {
            loginModal.classList.remove('hidden'); // Tampilkan modal login
        });

        closeModal.addEventListener('click', () => {
            loginModal.classList.add('hidden'); // Sembunyikan modal login
        });

        // Close modal when clicking outside of it
        document.addEventListener('click', (e) => {
            if (!loginModal.contains(e.target) && !loginBtn.contains(e.target)) {
                loginModal.classList.add('hidden');
            }
        });
    }

    
});
