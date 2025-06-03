document.addEventListener('DOMContentLoaded', function () {
  // Retrieve the stored values from localStorage
  const pickupDate = localStorage.getItem('pickupDate');
  const returnDate = localStorage.getItem('returnDate');
  
  // Check if the values exist and then update the summary fields
  if (pickupDate && returnDate) {
    document.getElementById('summary_pickup_date').textContent = pickupDate;
    document.getElementById('summary_return_date').textContent = returnDate;
  } else {
    console.error("Data not found in localStorage.");
  }
});