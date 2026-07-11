  function updateDateTime() {
            const now = new Date();
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dayName = days[now.getDay()];
            const monthName = months[now.getMonth()];
            const day = now.getDate().toString().padStart(2, '0');
            const year = now.getFullYear();
            const hours = now.getHours() % 12 || 12; // Convert to 12-hour format
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
            const time = `${hours}:${minutes} ${ampm}`;

            const datetime = `${dayName} | ${monthName} ${day}, ${year} ${time}`;
            document.getElementById('datetime').textContent = datetime;
        }

        // Update every second
        updateDateTime();
        setInterval(updateDateTime, 1000);