document.addEventListener('DOMContentLoaded', function () {
  const checkoutButton = document.querySelector('a[href="payment.html"]'); // The button or link to go to payment page

  checkoutButton.addEventListener('click', function (e) {
    // Prevent the default action of the link for now to store the data
    e.preventDefault();

    // Get the selected values from the form fields
    const pickupDate = document.getElementById('pickup_date').value;
    const pickupTime = document.getElementById('pickup_time').value;
    const returnDate = document.getElementById('return_date').value;
    const returnTime = document.getElementById('return_time').value;

    // Store the values in localStorage
    localStorage.setItem('pickupDate', pickupDate + ', ' + pickupTime);
    localStorage.setItem('returnDate', returnDate + ', ' + returnTime);

    // After saving the data, navigate to the payment page
    window.location.href = 'payment.html';
  });
});
