document.addEventListener("DOMContentLoaded", function () {
  // Initialize the day picker
  initializeDayPicker();

  // Clock update function
  updateClock();
  setInterval(updateClock, 1000);
});

function initializeDayPicker() {
  const days = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
  ];
  const dayPicker = document.getElementById("day-picker");

  if (!dayPicker) return;

  // Clear any existing options
  dayPicker.innerHTML = "";

  // Populate the day picker with options
  days.forEach((day, index) => {
    const option = document.createElement("option");
    option.value = index - 1; // Adjust to match PHP's day index (Monday = 0)
    if (index === 0) option.value = 6; // Sunday = 6 to match PHP indexing
    option.text = day;
    dayPicker.appendChild(option);
  });

  // Set the default value to the current day
  const currentDayIndex = new Date().getDay() - 1;
  dayPicker.value = currentDayIndex < 0 ? 6 : currentDayIndex;

  console.log("Day picker initialized with " + days.length + " days");
}

function getCurrentDayIndex() {
  let day = new Date().getDay() - 1; // 0 for Monday (PHP style)
  if (day < 0) day = 6; // Handle Sunday
  return day;
}

function updateScheduleForDay(dayIndex) {
  // Fade out current schedule
  const scheduleContainer = document.getElementById("schedule-container");
  scheduleContainer.classList.add("fade-out");

  setTimeout(() => {
    // Here you would fetch or filter the schedule data for the selected day
    // For now we'll just simulate a day change

    // Update schedule display based on the day index
    // This is a placeholder - you need to implement based on your data structure
    console.log(`Showing schedule for day: ${dayIndex}`);

    // Fade in updated schedule
    scheduleContainer.classList.remove("fade-out");
    scheduleContainer.classList.add("fade-in");

    setTimeout(() => {
      scheduleContainer.classList.remove("fade-in");
    }, 500);
  }, 500);
}

function updateClock() {
  const clockElement = document.getElementById("clock");
  if (clockElement) {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, "0");
    const minutes = now.getMinutes().toString().padStart(2, "0");
    const seconds = now.getSeconds().toString().padStart(2, "0");
    clockElement.textContent = `${hours}:${minutes}:${seconds}`;
  }
}
