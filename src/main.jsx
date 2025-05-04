import './main.jsx'

let users = [
    {
      id: 1,
      username: 'admin',
      email: 'admin@hotel.com',
      password: 'admin123',
      phone: '123-456-7890',
      age: 35,
      role: 'manager'
    },
    {
      id: 2,
      username: 'receptionist',
      email: 'reception@hotel.com',
      password: 'reception123',
      phone: '123-456-7891',
      age: 28,
      role: 'receptionist'
    },
    {
      id: 3,
      username: 'john',
      email: 'john@example.com',
      password: 'password123',
      phone: '123-456-7892',
      age: 32,
      role: 'user'
    }
  ];
  
  // Mock database for room data
  const rooms = [
    {
      id: 1,
      name: 'Luxury Suite',
      type: 'Suite',
      price: 299,
      bedSize: 'King',
      view: 'Ocean',
      capacity: 2,
      description: 'Enjoy our most luxurious suite with panoramic ocean views',
      amenities: ['Wi-Fi', 'Mini Bar', 'Jacuzzi', 'Room Service'],
      image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427'
    },
    {
      id: 2,
      name: 'Executive Room',
      type: 'Double',
      price: 199,
      bedSize: 'Queen',
      view: 'City',
      capacity: 2,
      description: 'Spacious room with modern amenities and city skyline views',
      amenities: ['Wi-Fi', 'Mini Bar', 'Room Service'],
      image: 'https://images.unsplash.com/photo-1566665797739-1674de7a421a'
    },
    {
      id: 3,
      name: 'Family Suite',
      type: 'Triple',
      price: 349,
      bedSize: 'King + Twin',
      view: 'Garden',
      capacity: 4,
      description: 'Perfect for families with separate living area and two bedrooms',
      amenities: ['Wi-Fi', 'Kitchen', 'Room Service', 'Kids Area'],
      image: 'https://images.unsplash.com/photo-1598928636135-d146006ff4be'
    },
    {
      id: 4,
      name: 'Standard Room',
      type: 'Single',
      price: 149,
      bedSize: 'Queen',
      view: 'City',
      capacity: 1,
      description: 'Comfortable room with all essential amenities for business or leisure',
      amenities: ['Wi-Fi', 'Room Service'],
      image: 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85'
    }
  ];
  
  // Mock database for reservations
  let reservations = [
    {
      id: 1,
      userId: 3,
      roomId: 1,
      checkIn: '2023-06-15',
      checkOut: '2023-06-18',
      guests: 2,
      status: 'accepted',
      package: 'Gold',
      housekeeping: true,
      housekeepingTime: '10:00',
      parking: true,
      totalPrice: 897,
    },
    {
      id: 2,
      userId: 3,
      roomId: 2,
      checkIn: '2023-07-10',
      checkOut: '2023-07-15',
      guests: 2,
      status: 'pending',
      package: 'Silver',
      housekeeping: true,
      housekeepingTime: '14:00',
      parking: false,
      totalPrice: 995,
    }
  ];
  
  // Current logged in user
  let currentUser = null;
  let loginAttempts = {};
  
  // DOM elements
  const root = document.getElementById('root');
  
  // Router
  const routes = {
    '/': renderHomePage,
    '/login': renderLoginPage,
    '/register': renderRegisterPage,
    '/dashboard': renderDashboard,
    '/rooms': renderRoomsPage,
    '/booking': renderBookingPage,
    '/confirmation': renderConfirmationPage
  };
  
  // Router function
  function navigateTo(path) {
    window.history.pushState({}, '', path);
    renderContent(path);
  }
  
  // Handle browser back/forward buttons
  window.addEventListener('popstate', () => {
    renderContent(window.location.pathname);
  });
  
  // Render the appropriate content based on URL
  function renderContent(path) {
    const render = routes[path] || renderNotFoundPage;
    render();
  }
  
  // Authentication functions
  function login(email, password, role) {
    // Check login attempts
    if (loginAttempts[email] && loginAttempts[email] >= 3) {
      return { success: false, message: "Account locked due to multiple failed attempts. Please reset your password." };
    }
    
    const user = users.find(u => u.email === email && u.password === password && u.role === role);
    
    if (user) {
      currentUser = user;
      // Reset login attempts on successful login
      loginAttempts[email] = 0;
      return { success: true };
    } else {
      // Increment login attempts
      loginAttempts[email] = (loginAttempts[email] || 0) + 1;
      return { success: false, message: `Invalid credentials. ${3 - loginAttempts[email]} attempts left.` };
    }
  }
  
  function register(userData) {
    // Check if email already exists
    if (users.find(u => u.email === userData.email)) {
      return { success: false, message: 'Email already registered' };
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(userData.email)) {
      return { success: false, message: 'Invalid email format' };
    }
    
    // Add new user with role 'user'
    const newUser = {
      id: users.length + 1,
      ...userData,
      role: 'user'
    };
    
    users.push(newUser);
    currentUser = newUser;
    
    return { success: true };
  }
  
  function logout() {
    currentUser = null;
    navigateTo('/');
  }
  
  // Page renderers
  function renderHeaderFooter(content) {
    return `
      <header class="header">
        <div class="container header-container">
          <h1 class="logo">Serene Hotel</h1>
          <nav>
            <ul class="nav-menu">
              <li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); navigateTo('/')">Home</a></li>
              <li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); navigateTo('/rooms')">Rooms</a></li>
              ${currentUser ? 
                `<li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); navigateTo('/dashboard')">Dashboard</a></li>
                 <li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); logout()">Logout</a></li>` 
                : 
                `<li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); navigateTo('/login')">Login</a></li>
                 <li class="nav-item"><a href="#" class="nav-link" onclick="event.preventDefault(); navigateTo('/register')">Register</a></li>`
              }
            </ul>
          </nav>
        </div>
      </header>
      <main>${content}</main>
      <footer class="footer">
        <div class="container">
          <p>&copy; ${new Date().getFullYear()} Serene Hotel. All rights reserved.</p>
        </div>
      </footer>
    `;
  }
  
  function renderHomePage() {
    const content = `
      <section class="hero">
        <div class="hero-content">
          <h1>Welcome to Serene Hotel</h1>
          <p>Experience luxury and comfort like never before</p>
          <button class="btn btn-secondary" onclick="navigateTo('/rooms')">Explore Rooms</button>
        </div>
      </section>
      
      <div class="container">
        <div class="search-form">
          <form id="search-form">
            <div class="search-row">
              <div class="search-group">
                <label for="check-in">Check-in Date</label>
                <input type="date" id="check-in" required>
              </div>
              
              <div class="search-group">
                <label for="check-out">Check-out Date</label>
                <input type="date" id="check-out" required>
              </div>
              
              <div class="search-group">
                <label for="guests">Number of Guests</label>
                <select id="guests" required>
                  <option value="1">1 Guest</option>
                  <option value="2">2 Guests</option>
                  <option value="3">3 Guests</option>
                  <option value="4">4 Guests</option>
                </select>
              </div>
            </div>
            
            <button type="submit" class="btn search-btn">Search Availability</button>
          </form>
        </div>
        
        <div class="rooms-container">
          <h2 class="section-title">Featured Rooms</h2>
          <div class="rooms-grid">
            ${rooms.slice(0, 3).map(room => `
              <div class="room-card">
                <div class="room-image" style="background-image: url('${room.image}')"></div>
                <div class="room-details">
                  <h3 class="room-title">${room.name}</h3>
                  <p class="room-price">$${room.price} <span>/ night</span></p>
                  <div class="room-features">
                    <span class="room-feature">${room.capacity} Guests</span>
                    <span class="room-feature">${room.bedSize} Bed</span>
                    <span class="room-feature">${room.view} View</span>
                  </div>
                  <button class="btn" onclick="navigateTo('/rooms')">View Details</button>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Add event listener for search form
    document.getElementById('search-form').addEventListener('submit', function(e) {
      e.preventDefault();
      navigateTo('/rooms');
    });
  }
  
  function renderLoginPage() {
    const content = `
      <div class="auth-container">
        <div class="auth-image"></div>
        <div class="auth-form-container">
          <form class="auth-form" id="login-form">
            <h2>Login to Your Account</h2>
            <div id="login-alert"></div>
            
            <div class="tab-container">
              <div class="tabs">
                <div class="tab active" data-role="user">Guest</div>
                <div class="tab" data-role="receptionist">Receptionist</div>
                <div class="tab" data-role="manager">Manager</div>
              </div>
            </div>
            
            <input type="hidden" id="role" value="user">
            
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" required>
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" required>
            </div>
            
            <button type="submit" class="btn auth-btn">Login</button>
            
            <div class="auth-switch">
              <p>Don't have an account? <a href="#" onclick="event.preventDefault(); navigateTo('/register')">Register</a></p>
            </div>
          </form>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Add tab functionality
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('role').value = this.dataset.role;
      });
    });
    
    // Add event listener for login form
    document.getElementById('login-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      const role = document.getElementById('role').value;
      
      const result = login(email, password, role);
      
      if (result.success) {
        navigateTo('/dashboard');
      } else {
        const alertDiv = document.getElementById('login-alert');
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = result.message;
      }
    });
  }
  
  function renderRegisterPage() {
    const content = `
      <div class="auth-container">
        <div class="auth-image"></div>
        <div class="auth-form-container">
          <form class="auth-form" id="register-form">
            <h2>Create Your Account</h2>
            <div id="register-alert"></div>
            
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" required>
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" required>
            </div>
            
            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input type="tel" id="phone" required>
            </div>
            
            <div class="form-group">
              <label for="age">Age</label>
              <input type="number" id="age" required min="18">
            </div>
            
            <button type="submit" class="btn auth-btn">Register</button>
            
            <div class="auth-switch">
              <p>Already have an account? <a href="#" onclick="event.preventDefault(); navigateTo('/login')">Login</a></p>
            </div>
          </form>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Add event listener for register form
    document.getElementById('register-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const userData = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        phone: document.getElementById('phone').value,
        age: parseInt(document.getElementById('age').value)
      };
      
      const result = register(userData);
      
      if (result.success) {
        navigateTo('/dashboard');
      } else {
        const alertDiv = document.getElementById('register-alert');
        alertDiv.className = 'alert alert-error';
        alertDiv.textContent = result.message;
      }
    });
  }
  
  function renderDashboard() {
    // Redirect to login if not authenticated
    if (!currentUser) {
      navigateTo('/login');
      return;
    }
    
    let dashboardContent = '';
    
    if (currentUser.role === 'user') {
      // User dashboard
      const userReservations = reservations.filter(r => r.userId === currentUser.id);
      
      dashboardContent = `
        <h2>Welcome, ${currentUser.username}!</h2>
        <div class="dashboard-stats">
          <div class="stat-card">
            <h3>Your Reservations</h3>
            <p class="stat-number">${userReservations.length}</p>
          </div>
        </div>
        
        <h3>Your Reservations</h3>
        ${userReservations.length > 0 ? `
          <table class="reservation-table">
            <thead>
              <tr>
                <th>Room</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Status</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              ${userReservations.map(reservation => {
                const room = rooms.find(r => r.id === reservation.roomId);
                return `
                  <tr>
                    <td>${room.name}</td>
                    <td>${reservation.checkIn}</td>
                    <td>${reservation.checkOut}</td>
                    <td><span class="status status-${reservation.status}">${reservation.status}</span></td>
                    <td>$${reservation.totalPrice}</td>
                  </tr>
                `;
              }).join('')}
            </tbody>
          </table>
        ` : '<p>You have no reservations yet.</p>'}
        
        <div class="actions">
          <button class="btn" onclick="navigateTo('/rooms')">Browse Rooms</button>
        </div>
      `;
    } else if (currentUser.role === 'receptionist') {
      // Receptionist dashboard
      dashboardContent = `
        <h2>Receptionist Dashboard</h2>
        
        <div class="tab-container">
          <div class="tabs">
            <div class="tab active" data-tab="pending">Pending</div>
            <div class="tab" data-tab="accepted">Accepted</div>
            <div class="tab" data-tab="all">All Reservations</div>
          </div>
          
          <div class="tab-content">
            <div class="tab-pane active" id="pending-tab">
              <h3>Pending Reservations</h3>
              ${renderReservationsTable(reservations.filter(r => r.status === 'pending'))}
            </div>
            
            <div class="tab-pane" id="accepted-tab">
              <h3>Accepted Reservations</h3>
              ${renderReservationsTable(reservations.filter(r => r.status === 'accepted'))}
            </div>
            
            <div class="tab-pane" id="all-tab">
              <h3>All Reservations</h3>
              ${renderReservationsTable(reservations)}
            </div>
          </div>
        </div>
      `;
    } else if (currentUser.role === 'manager') {
      // Manager dashboard
      dashboardContent = `
        <h2>Manager Dashboard</h2>
        
        <div class="dashboard-stats">
          <div class="stat-card">
            <h3>Total Reservations</h3>
            <p class="stat-number">${reservations.length}</p>
          </div>
          <div class="stat-card">
            <h3>Pending</h3>
            <p class="stat-number">${reservations.filter(r => r.status === 'pending').length}</p>
          </div>
          <div class="stat-card">
            <h3>Accepted</h3>
            <p class="stat-number">${reservations.filter(r => r.status === 'accepted').length}</p>
          </div>
        </div>
        
        <div class="tab-container">
          <div class="tabs">
            <div class="tab active" data-tab="reservations">Reservations</div>
            <div class="tab" data-tab="rooms">Rooms</div>
            <div class="tab" data-tab="staff">Staff</div>
          </div>
          
          <div class="tab-content">
            <div class="tab-pane active" id="reservations-tab">
              <h3>All Reservations</h3>
              ${renderReservationsTable(reservations)}
            </div>
            
            <div class="tab-pane" id="rooms-tab">
              <h3>Room Management</h3>
              <table class="reservation-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Room Name</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Capacity</th>
                  </tr>
                </thead>
                <tbody>
                  ${rooms.map(room => `
                    <tr>
                      <td>${room.id}</td>
                      <td>${room.name}</td>
                      <td>${room.type}</td>
                      <td>$${room.price}</td>
                      <td>${room.capacity}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
            
            <div class="tab-pane" id="staff-tab">
              <h3>Staff Management</h3>
              <table class="reservation-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                  </tr>
                </thead>
                <tbody>
                  ${users.filter(user => user.role !== 'user').map(user => `
                    <tr>
                      <td>${user.id}</td>
                      <td>${user.username}</td>
                      <td>${user.email}</td>
                      <td>${user.role}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
              <button class="btn" style="margin-top: 20px;">Add New Staff</button>
            </div>
          </div>
        </div>
      `;
    }
    
    const content = `
      <div class="dashboard">
        <aside class="sidebar">
          <ul class="sidebar-menu">
            <li class="sidebar-item">
              <a href="#" class="sidebar-link active">Dashboard</a>
            </li>
            ${currentUser.role === 'user' ? `
              <li class="sidebar-item">
                <a href="#" class="sidebar-link" onclick="event.preventDefault(); navigateTo('/rooms')">Browse Rooms</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">My Reservations</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Profile Settings</a>
              </li>
            ` : currentUser.role === 'receptionist' ? `
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Reservations</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Check-ins</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Check-outs</a>
              </li>
            ` : `
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Reservations</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Room Management</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Staff Management</a>
              </li>
              <li class="sidebar-item">
                <a href="#" class="sidebar-link">Reports</a>
              </li>
            `}
          </ul>
        </aside>
        
        <div class="main-content">
          ${dashboardContent}
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Add tab functionality
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', function() {
        const tabContainer = this.closest('.tab-container');
        const tabName = this.dataset.tab;
        
        // Set active tab
        tabContainer.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        // Show active content
        tabContainer.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.getElementById(`${tabName}-tab`).classList.add('active');
      });
    });
  }
  
  function renderReservationsTable(reservationsList) {
    if (reservationsList.length === 0) {
      return '<p>No reservations found.</p>';
    }
    
    return `
      <table class="reservation-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Room</th>
            <th>Guest</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody>
          ${reservationsList.map(reservation => {
            const room = rooms.find(r => r.id === reservation.roomId);
            const user = users.find(u => u.id === reservation.userId);
            return `
              <tr>
                <td>${reservation.id}</td>
                <td>${room.name}</td>
                <td>${user ? user.username : 'Unknown'}</td>
                <td>${reservation.checkIn}</td>
                <td>${reservation.checkOut}</td>
                <td><span class="status status-${reservation.status}">${reservation.status}</span></td>
                <td>$${reservation.totalPrice}</td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    `;
  }
  
  function renderRoomsPage() {
    const content = `
      <div class="container">
        <h2 class="section-title">Our Rooms & Suites</h2>
        
        <div class="search-form">
          <form id="search-form">
            <div class="search-row">
              <div class="search-group">
                <label for="check-in">Check-in Date</label>
                <input type="date" id="check-in" required>
              </div>
              
              <div class="search-group">
                <label for="check-out">Check-out Date</label>
                <input type="date" id="check-out" required>
              </div>
              
              <div class="search-group">
                <label for="guests">Number of Guests</label>
                <select id="guests" required>
                  <option value="1">1 Guest</option>
                  <option value="2">2 Guests</option>
                  <option value="3">3 Guests</option>
                  <option value="4">4 Guests</option>
                </select>
              </div>
              
              <div class="search-group">
                <label for="room-type">Room Type</label>
                <select id="room-type">
                  <option value="">All Types</option>
                  <option value="Suite">Suite</option>
                  <option value="Single">Single</option>
                  <option value="Double">Double</option>
                  <option value="Triple">Triple</option>
                </select>
              </div>
            </div>
            
            <button type="submit" class="btn search-btn">Update Search</button>
          </form>
        </div>
        
        <div class="rooms-grid">
          ${rooms.map(room => `
            <div class="room-card">
              <div class="room-image" style="background-image: url('${room.image}')"></div>
              <div class="room-details">
                <h3 class="room-title">${room.name}</h3>
                <p class="room-price">$${room.price} <span>/ night</span></p>
                <div class="room-features">
                  <span class="room-feature">${room.capacity} Guests</span>
                  <span class="room-feature">${room.bedSize} Bed</span>
                  <span class="room-feature">${room.view} View</span>
                </div>
                <p>${room.description}</p>
                <button class="btn" data-room-id="${room.id}" onclick="startBooking(${room.id})">Book Now</button>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Set min date for check-in and check-out
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('check-in').setAttribute('min', today);
    document.getElementById('check-out').setAttribute('min', today);
    
    // Add event listener for search form
    document.getElementById('search-form').addEventListener('submit', function(e) {
      e.preventDefault();
      // In a real app, this would filter rooms based on search criteria
      // For this demo, we'll just show all rooms
    });
  }
  
  // Store booking details between pages
  let currentBooking = {};
  
  function startBooking(roomId) {
    if (!currentUser) {
      navigateTo('/login');
      return;
    }
    
    const room = rooms.find(r => r.id === roomId);
    
    // Set initial booking details
    currentBooking = {
      roomId: room.id,
      userId: currentUser.id,
      room: room
    };
    
    navigateTo('/booking');
  }
  
  function renderBookingPage() {
    // Redirect if not authenticated or no room selected
    if (!currentUser) {
      navigateTo('/login');
      return;
    }
    
    if (!currentBooking.roomId) {
      navigateTo('/rooms');
      return;
    }
    
    const room = currentBooking.room;
    
    const content = `
      <div class="container">
        <h2>Complete Your Booking</h2>
        
        <div class="booking-container">
          <div class="booking-room-details">
            <div class="room-image" style="background-image: url('${room.image}')"></div>
            <h3>${room.name}</h3>
            <p class="room-price">$${room.price} <span>/ night</span></p>
            <div class="room-features">
              <span class="room-feature">${room.capacity} Guests</span>
              <span class="room-feature">${room.bedSize} Bed</span>
              <span class="room-feature">${room.view} View</span>
            </div>
            <p>${room.description}</p>
          </div>
          
          <form id="booking-form" class="booking-form">
            <div class="form-group">
              <label for="check-in">Check-in Date</label>
              <input type="date" id="check-in" required>
            </div>
            
            <div class="form-group">
              <label for="check-out">Check-out Date</label>
              <input type="date" id="check-out" required>
            </div>
            
            <div class="form-group">
              <label for="guests">Number of Guests</label>
              <select id="guests" required>
                <option value="1">1 Guest</option>
                <option value="2" selected>2 Guests</option>
                <option value="3">3 Guests</option>
                <option value="4">4 Guests</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="package">Package</label>
              <select id="package" required>
                <option value="Silver">Silver - Standard amenities</option>
                <option value="Gold">Gold - Premium amenities + Breakfast</option>
                <option value="Platinum">Platinum - All inclusive</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Housekeeping</label>
              <div>
                <input type="checkbox" id="housekeeping" checked>
                <label for="housekeeping">Yes, I want housekeeping service</label>
              </div>
            </div>
            
            <div class="form-group" id="housekeeping-time-group">
              <label for="housekeeping-time">Preferred Housekeeping Time</label>
              <select id="housekeeping-time">
                <option value="09:00">09:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">01:00 PM</option>
                <option value="14:00">02:00 PM</option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Parking Spot</label>
              <div>
                <input type="checkbox" id="parking">
                <label for="parking">I need a parking spot ($15/day)</label>
              </div>
            </div>
            
            <div class="form-group">
              <label>Payment Method</label>
              <div>
                <input type="radio" id="payment-online" name="payment" value="online" checked>
                <label for="payment-online">Pay online (Credit Card)</label>
              </div>
              <div>
                <input type="radio" id="payment-cash" name="payment" value="cash">
                <label for="payment-cash">Pay at hotel (Requires deposit)</label>
              </div>
            </div>
            
            <div id="total-price" class="total-price">
              <p>Total: $<span id="price-amount">${room.price}</span></p>
            </div>
            
            <button type="submit" class="btn">Complete Booking</button>
          </form>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Set min date for check-in and check-out
    const today = new Date().toISOString().split('T')[0];
    const checkIn = document.getElementById('check-in');
    const checkOut = document.getElementById('check-out');
    
    checkIn.setAttribute('min', today);
    checkIn.value = today;
    
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    
    checkOut.setAttribute('min', tomorrowStr);
    checkOut.value = tomorrowStr;
    
    // Show/hide housekeeping time based on checkbox
    document.getElementById('housekeeping').addEventListener('change', function() {
      document.getElementById('housekeeping-time-group').style.display = this.checked ? 'block' : 'none';
    });
    
    // Calculate total price
    function calculateTotal() {
      const checkInDate = new Date(checkIn.value);
      const checkOutDate = new Date(checkOut.value);
      const nights = (checkOutDate - checkInDate) / (1000 * 60 * 60 * 24);
      
      if (nights <= 0) {
        return;
      }
      
      let total = room.price * nights;
      
      // Add package price
      const packageSelect = document.getElementById('package');
      if (packageSelect.value === 'Gold') {
        total += 30 * nights;
      } else if (packageSelect.value === 'Platinum') {
        total += 60 * nights;
      }
      
      // Add parking
      if (document.getElementById('parking').checked) {
        total += 15 * nights;
      }
      
      document.getElementById('price-amount').textContent = total.toFixed(0);
    }
    
    // Add event listeners for price calculation
    checkIn.addEventListener('change', calculateTotal);
    checkOut.addEventListener('change', calculateTotal);
    document.getElementById('package').addEventListener('change', calculateTotal);
    document.getElementById('parking').addEventListener('change', calculateTotal);
    
    // Initial calculation
    calculateTotal();
    
    // Add event listener for booking form
    document.getElementById('booking-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Collect booking data
      currentBooking = {
        ...currentBooking,
        checkIn: document.getElementById('check-in').value,
        checkOut: document.getElementById('check-out').value,
        guests: parseInt(document.getElementById('guests').value),
        package: document.getElementById('package').value,
        housekeeping: document.getElementById('housekeeping').checked,
        housekeepingTime: document.getElementById('housekeeping').checked ? document.getElementById('housekeeping-time').value : null,
        parking: document.getElementById('parking').checked,
        paymentMethod: document.querySelector('input[name="payment"]:checked').value,
        totalPrice: parseFloat(document.getElementById('price-amount').textContent)
      };
      
      // Create reservation
      const newReservation = {
        id: reservations.length + 1,
        userId: currentUser.id,
        roomId: currentBooking.roomId,
        checkIn: currentBooking.checkIn,
        checkOut: currentBooking.checkOut,
        guests: currentBooking.guests,
        status: 'pending',
        package: currentBooking.package,
        housekeeping: currentBooking.housekeeping,
        housekeepingTime: currentBooking.housekeepingTime,
        parking: currentBooking.parking,
        totalPrice: currentBooking.totalPrice
      };
      
      reservations.push(newReservation);
      
      navigateTo('/confirmation');
    });
  }
  
  function renderConfirmationPage() {
    if (!currentBooking.totalPrice) {
      navigateTo('/rooms');
      return;
    }
    
    const room = currentBooking.room;
    
    const content = `
      <div class="container">
        <div class="confirmation">
          <h2>Booking Confirmation</h2>
          
          <div class="alert alert-success">
            <p>Your booking has been received and is pending confirmation.</p>
          </div>
          
          <div class="booking-details">
            <h3>Booking Details</h3>
            
            <div class="booking-info">
              <div class="booking-info-item">
                <span class="label">Room:</span>
                <span class="value">${room.name}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Check-in:</span>
                <span class="value">${currentBooking.checkIn}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Check-out:</span>
                <span class="value">${currentBooking.checkOut}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Guests:</span>
                <span class="value">${currentBooking.guests}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Package:</span>
                <span class="value">${currentBooking.package}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Housekeeping:</span>
                <span class="value">${currentBooking.housekeeping ? `Yes (${currentBooking.housekeepingTime})` : 'No'}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Parking:</span>
                <span class="value">${currentBooking.parking ? 'Yes' : 'No'}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Payment Method:</span>
                <span class="value">${currentBooking.paymentMethod === 'online' ? 'Credit Card' : 'At Hotel'}</span>
              </div>
              
              <div class="booking-info-item">
                <span class="label">Total Price:</span>
                <span class="value">$${currentBooking.totalPrice}</span>
              </div>
            </div>
          </div>
          
          <div class="booking-actions">
            <button class="btn" onclick="navigateTo('/dashboard')">Go to Dashboard</button>
          </div>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
    
    // Clear current booking after confirmation
    setTimeout(() => {
      currentBooking = {};
    }, 1000);
  }
  
  function renderNotFoundPage() {
    const content = `
      <div class="container">
        <div class="not-found">
          <h2>404 - Page Not Found</h2>
          <p>The page you are looking for does not exist.</p>
          <button class="btn" onclick="navigateTo('/')">Go to Home</button>
        </div>
      </div>
    `;
    
    root.innerHTML = renderHeaderFooter(content);
  }
  
  // Expose functions to global scope for event handling
  window.navigateTo = navigateTo;
  window.login = login;
  window.register = register;
  window.logout = logout;
  window.startBooking = startBooking;
  
  // Initialize the app
  document.addEventListener('DOMContentLoaded', () => {
    renderContent(window.location.pathname);
  });
  