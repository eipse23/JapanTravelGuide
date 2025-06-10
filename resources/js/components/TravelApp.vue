<template>
  <div>
    <!-- App Header Section -->
    <header class="app-header">
      <h1>Japan Travel Guide</h1>
      <div class="header-controls">
        <!-- City Selection Dropdown -->
        <select v-model="selectedCity" @change="fetchData" aria-label="Select City">
          <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
        </select>
        <!-- Scroll Button to Popular Places -->
        <button @click="scrollToPlaces" aria-label="Scroll to popular places" class="scroll-down-btn" title="Scroll down to see popular places.">↓</button>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="container">
      <!-- Weather Forecast Section -->
      <section class="forecast" aria-labelledby="forecast-title">
        <h2 id="forecast-title" class="section-title">{{ selectedCity }} 5-Day Weather Forecast</h2>

        <!-- Loading Indicator for Weather Data -->
        <div v-if="isLoadingWeather" class="loading" role="status" aria-live="polite">
          <span class="loading-spinner" aria-hidden="true"></span> Loading weather data...
        </div>

        <!-- No Data Available Message -->
        <div v-else-if="dailyForecast.length === 0" class="no-data" role="alert">No forecast available.</div>

        <!-- Weather Forecast Display -->
        <div v-else class="days">
          <article v-for="(day, index) in dailyForecast" :key="index" class="day" :aria-label="'Weather forecast for ' + day.date">
            <header class="date">{{ day.date }}</header>
            <div class="forecast-detail">
              <div v-for="(timeBlock, idx) in day.timeBlocks" :key="idx" class="time-block" :title="timeBlock.description">
                <time class="time" :datetime="timeBlock.time">{{ timeBlock.time }}</time>
                <img :src="timeBlock.icon" :alt="timeBlock.description" loading="lazy" />
                <p class="desc">{{ timeBlock.description }}</p>
                <p class="temp">{{ timeBlock.temp }}°C</p>
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- Popular Places Section -->
      <section class="places" aria-labelledby="places-title">
        <h2 id="places-title" class="section-title">{{ selectedCity }} Popular Places</h2>

        <!-- Loading Indicator for Places Data -->
        <div v-if="isLoadingPlaces" class="loading" role="status" aria-live="polite">
          <span class="loading-spinner" aria-hidden="true"></span> Loading places...
        </div>

        <!-- No Data Available Message -->
        <div v-else-if="places.length === 0" class="no-data" role="alert">No popular places found.</div>

        <!-- Places Display -->
        <div v-else class="places-grid">
          <article v-for="place in places" :key="place.id" class="place" tabindex="0" :aria-label="'Details for ' + place.name">
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
            <div class="place-details">
              <h3 class="place-name">
                <a
                  v-if="place.geocodes?.main?.latitude && place.geocodes?.main?.longitude"
                  :href="`https://www.google.com/maps?q=${place.geocodes.main.latitude},${place.geocodes.main.longitude}`"
                  target="_blank" rel="noopener noreferrer"
                  aria-label="View location on Google Maps"
                >
                  {{ place.name }}
                </a>
              </h3>

              <!-- Place Address -->
              <p class="place-location">
                <i class="fas fa-address-card icon-size" aria-hidden="true"></i>
                <strong>Address:</strong> {{ place.location.formatted_address || 'N/A' }}
              </p>

              <!-- Place Region -->
              <p class="place-location">
                <i class="fas fa-flag icon-size" aria-hidden="true"></i>
                <strong>Region:</strong> {{ place.location.region || 'N/A' }}
              </p>

              <!-- Place Locality -->
              <p class="place-location">
                <i class="fas fa-city icon-size" aria-hidden="true"></i>
                <strong>Locality:</strong> {{ place.location.locality || 'N/A' }}
              </p>

              <!-- Place Category -->
              <p class="place-category">
                <i class="fas fa-tags icon-size" aria-hidden="true"></i>
                <strong>Category:</strong> {{ place.categories[0]?.name || 'N/A' }}
              </p>

              <!-- Place Status -->
              <p class="place-category">
                <i class="fas fa-info-circle icon-size" aria-hidden="true"></i>
                <strong>Status:</strong> {{ place.closed_bucket || 'N/A' }}
              </p>

              <!-- Place Rating -->
              <p class="place-rating">
                <i class="fas fa-star icon-size" aria-hidden="true"></i>
                <strong>Rating:</strong>
                <span v-if="place.rating" class="rating-stars" aria-label="Rating: {{ place.rating }} out of 5">{{ '⭐'.repeat(Math.round(place.rating)) }}</span>
                <span v-else>N/A</span>
              </p>

              <!-- Google Maps Link -->
              <p class="place-map">
                <a
                  v-if="place.geocodes?.main?.latitude && place.geocodes?.main?.longitude"
                  :href="`https://www.google.com/maps?q=${place.geocodes.main.latitude},${place.geocodes.main.longitude}`"
                  target="_blank" rel="noopener noreferrer"
                  aria-label="View location on Google Maps"
                >
                  View on Google Maps
                </a>
                <span v-else>Location not available</span>
              </p>
            </div>
          </article>
        </div>
      </section>
    </main>

    <footer class="app-footer" role="contentinfo">
        <div class="footer-container">
            <p>© 2025 Japan Travel Guide. All rights reserved.</p>
            <nav aria-label="Footer navigation">
            <ul class="footer-links">
                <li><a href="#forecast-title">Weather Forecast</a></li>
                <li><a href="#places-title">Popular Places</a></li>
            </ul>
            </nav>
        </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// Define the list of cities available for selection
const cities = ['Tokyo', 'Osaka', 'Kyoto', 'Hokkaido', 'Fukuoka']

// Reactive reference to the currently selected city
const selectedCity = ref(cities[0])

// Reactive references to store weather and places data
const dailyForecast = ref([])
const places = ref([])

// Reactive flags to manage loading states
const isLoadingWeather = ref(true)
const isLoadingPlaces = ref(true)

// Function to scroll smoothly to the "Popular Places" section
const scrollToPlaces = () => {
  const section = document.querySelector('.places')
  section?.scrollIntoView({ behavior: 'smooth' })
}

// Utility function to decode Unicode characters in strings
const decodeUnicode = (str) => {
  try {
    return JSON.parse(`"${str}"`)
  } catch {
    return str
  }
}

// Function to fetch weather and places data based on the selected city
const fetchData = async () => {
  // Set loading states to true before fetching data
  isLoadingWeather.value = true
  isLoadingPlaces.value = true

  try {
    // Fetch weather forecast data
    const weatherRes = await axios.get(`/api/forecast/${selectedCity.value}`)
    const forecastList = weatherRes.data.list || []
    dailyForecast.value = processForecastData(forecastList)
  } catch {
    dailyForecast.value = []
  } finally {
    isLoadingWeather.value = false
  }

  try {
    // Fetch popular places data
    const placesRes = await axios.get(`/api/places/${selectedCity.value}`)
    places.value = (placesRes.data.results || []).map(place => ({
      ...place,
      address: decodeUnicode(place.address)
    }))
  } catch {
    places.value = []
  } finally {
    isLoadingPlaces.value = false
  }
}

// Helper function to process and structure forecast data
const processForecastData = (forecastList) => {
  const days = []
  let currentDay = null

  forecastList.forEach((forecast) => {
    const date = new Date(forecast.dt * 1000)
    const dayKey = date.toDateString()

    // Start a new day if the date changes
    if (!currentDay || currentDay.date !== dayKey) {
      if (currentDay) days.push(currentDay)
      currentDay = {
        date: dayKey,
        timeBlocks: [],
      }
    }

    // Add time block data for the current day
    currentDay.timeBlocks.push({
      time: date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      icon: `http://openweathermap.org/img/wn/${forecast.weather?.[0]?.icon || '01d'}.png`,
      description: forecast.weather?.[0]?.description || 'N/A',
      temp: forecast.main?.temp !== undefined ? Math.round(forecast.main.temp) : 'N/A',
    })
  })

  // Push the last day to the days array
  if (currentDay) days.push(currentDay)
  return days
}

// Fetch data when the component is mounted
onMounted(fetchData)
</script>

<style scoped>
  /* ========================================
     Global Styles
     ======================================== */

  /* Apply box-sizing border-box model universally */
  * {
    box-sizing: border-box;
  }

  /* Set body margin to 0 and define font family */
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f6f9fc;
    color: #222;
  }

  /* ========================================
     Header Section
     ======================================== */

  /* Style for the application header */
  .app-header {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
    margin-bottom: 1rem;
  }

  /* Style for the header title */
  .app-header h1 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: 1.5px;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
  }

  /* ========================================
     Header Controls
     ======================================== */

  /* Container for header controls */
  .header-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  /* Style for select dropdown */
  select {
    font-size: 0.9rem;
    padding: 0.4rem 0.9rem;
    border-radius: 8px;
    border: 2px solid #3b82f6;
    background: #f0f7ff;
    color: #1e3a8a;
    cursor: pointer;
    min-width: 140px;
    box-shadow: inset 1px 1px 4px rgba(59, 130, 246, 0.15);
    transition: border-color 0.3s ease, background-color 0.3s ease;
  }

  /* Hover and focus states for select dropdown */
  select:hover,
  select:focus {
    border-color: #1e40af;
    background: #dbeafe;
    outline: none;
  }

  .scroll-down-btn {
    cursor: pointer;
  }

  /* ========================================
     Container Styling
     ======================================== */

  /* Main container for content */
  .container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1rem 1.5rem;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
  }

  /* ========================================
     City Selection
     ======================================== */

  /* Centered city selection */
  .city-select {
    text-align: center;
    margin-bottom: 1rem;
  }

  /* ========================================
     Scroll Button
     ======================================== */

  /* Container for scroll button */
  .scroll-button {
    text-align: center;
    margin-bottom: 1.5rem;
  }

  /* Style for scroll button */
  .scroll-button button {
    background: #3b82f6;
    color: white;
    padding: 0.5rem 1.4rem;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.5);
    transition: background-color 0.3s ease;
    user-select: none;
  }

  /* Hover and focus states for scroll button */
  .scroll-button button:hover,
  .scroll-button button:focus {
    background: #1e40af;
    outline: none;
  }

  /* ========================================
     Section Title
     ======================================== */

  /* Style for section titles */
  .section-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 1rem;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 0.25rem;
  }

  /* ========================================
     Loading and No Data
     ======================================== */

  /* Style for loading and no data messages */
  .loading,
  .no-data {
    text-align: center;
    color: #666;
    font-style: italic;
    font-size: 0.9rem;
    padding: 0.75rem 0;
  }

  /* ========================================
     Forecast Section
     ======================================== */

  /* Grid layout for forecast days */
  .forecast .days {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
  }

  /* Style for each forecast day */
  .day {
    background: #e0f0ff;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(59, 130, 246, 0.25);
    padding: 0.75rem 0.9rem;
    display: flex;
    flex-direction: column;
    user-select: none;
  }

  /* Style for date in forecast */
  .date {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0.4rem;
    color: #1e40af;
    user-select: text;
  }

  /* Forecast detail container */
  .forecast-detail {
    display: flex;
    gap: 0.6rem;
    overflow-x: auto;
    padding-bottom: 0.3rem;
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 transparent;
  }

  /* Custom scrollbar for forecast detail */
  .forecast-detail::-webkit-scrollbar {
    height: 6px;
  }

  .forecast-detail::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 3px;
  }

  /* Time block styling */
  .time-block {
    min-width: 80px;
    background:rgb(133, 173, 237);
    border-radius: 7px;
    box-shadow: 0 1.5px 4px rgba(0, 0, 0, 0.07);
    padding: 0.4rem 0.6rem;
    text-align: center;
    user-select: none;
  }

  .time-block:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    background: gray;
    color: #fff!important;
  }

  .time-block:hover .desc {
    color: #fff; /* Tomato red */
    transition: color 0.3s ease; /* Smooth transition */
  }

  /* Time label styling */
  .time {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1e40af;
    margin-bottom: 0.3rem;
    display: block;
  }

  /* Time block icon size */
  .time-block img {
    width: 30px;
    height: 30px;
    margin-bottom: 0.3rem;
    user-select: none;
    margin:0 auto;
  }

  /* Description text styling */
  .desc {
    font-size: 0.75rem;
    font-style: italic;
    margin: 0 0 0.2rem;
    color: #444;
  }

  /* Temperature styling */
  .temp {
    font-weight: 600;
    font-size: 0.9rem;
    color: #1e40af;
    user-select: text;
  }

  /* Places grid layout */
  .places {
    margin-top: 2rem;
  }
  
  .places-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px;
    margin-top: 1rem;
  }

  /* Individual place styling */
  .place {
    background: #fff;
    box-shadow: 0 5px 16px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    padding: 0.75rem 0.9rem;
    display: flex;
    gap: 0.6rem;
    align-items: flex-start;
    cursor: default;
    user-select: none;
    transition: box-shadow 0.2s ease;
  }

  /* Hover and focus states for place */
  .place:focus,
  .place:hover {
    box-shadow: 0 7px 24px rgba(59, 130, 246, 0.6);
    outline: none;
  }

  /* Map marker icon styling */
  .place i.fas.fa-map-marker-alt {
    font-size: 1.2rem;
    color: #3b82f6;
    margin-top: 0.2rem;
    flex-shrink: 0;
    user-select: none;
  }

  /* Place details styling */
  .place-details {
    flex: 1;
    font-size: 0.9rem;
    color: #222;
  }

  /* Place name styling */
  .place-name {
    margin: 0 0 0.4rem;
    font-weight: 700;
    font-size: 1.1rem;
    color: #1e3a8a;
    user-select: text;
  }

  /* Place location, category, rating, and map styling */
  .place-location,
  .place-category,
  .place-rating,
  .place-map {
    margin: 0.15rem 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #555;
  }

  /* Icon size styling */
  .icon-size {
    font-size: 0.85rem;
    color: #3b82f6;
    user-select: none;
  }

  /* Rating stars styling */
  .rating-stars {
    color: #fbbf24;
    font-weight: 700;
    user-select: none;
  }

  /* Map link styling */
  .place-map a {
    color: #3b82f6;
    font-weight: 600;
    text-decoration: none;
    user-select: text;
  }

  /* Hover and focus states for map link */
  .place-map a:hover,
  .place-map a:focus {
    text-decoration: underline;
    outline: none;
  }

  /* ========================================
     Responsive Tweaks
     ======================================== */

  /* Media query for screens with max width of 600px */
  @media (max-width: 600px) {
    .forecast .days,
    .places-grid {
      grid-template-columns: 1fr;
      
    }
    .day{
      width:100%!important;
      overflow-x: auto!important;
    }

    /* Adjust gap for forecast details */
    .forecast-detail {
      gap: 0.4rem;
    }

    /* Adjust min-width for time blocks */
    .time-block {
      min-width: 70px;
    }
    .footer-container {
     flex-direction: column;
     gap: 0.5rem;
     height:60px!important;
    }

  .footer-links {
    display: block;
    text-align: left;
  }

  .footer-links li {
    display: block;
    margin: 5px 0;
  }
  }

  /* Footer Styles */
.app-footer {
  background: linear-gradient(90deg, #1e3a8a, #3b82f6);
  color: #fff;
  padding: 1rem 1.5rem;
  margin-top: 2rem;
  font-size: 0.9rem;
  box-shadow: 0 -4px 10px rgba(59, 130, 246, 0.3);
  user-select: none;
}

.footer-container {
  max-width: 1000px;
  margin: 0 auto;
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 0.8rem;
}

.footer-container {
  /* Change from justify-content: space-between; */
  align-items: center;
  gap: 0.8rem;
}

.footer-links {
  flex-wrap: wrap;
  gap: 1rem;
  flex: 2 1 auto;
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
}

.footer-links li {
  margin: 0;
}

.footer-links a {
  color: #bbd7ff;
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-links a:hover,
.footer-links a:focus {
  color: #fff;
  text-decoration: underline;
  outline: none;
}
.footer-links::after {
  content: "";
  display: block;
  clear: both;
}
</style>


 
