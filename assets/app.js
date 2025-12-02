// Navigation hamburger toggle
const navToggle = document.querySelector(".nav-toggle");
const navLinks = document.querySelector(".nav-links");

if (navToggle && navLinks) {
  navToggle.addEventListener("click", () => {
    const isOpen = navLinks.classList.toggle("nav-open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

// Logo click - reload page
const logo = document.querySelector(".logo");
if (logo) {
  logo.addEventListener("click", (e) => {
    // If already on index.php, just scroll to top
    if (window.location.pathname.endsWith("index.php") || window.location.pathname === "/" || window.location.pathname.endsWith("/")) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
      // Reload the page to reset state
      setTimeout(() => {
        window.location.reload();
      }, 100);
    }
  });
}

// Home link - scroll to top and reload if already on page
const homeLinks = document.querySelectorAll('a[href="index.php"], a[href="/"]');
homeLinks.forEach(link => {
  link.addEventListener("click", (e) => {
    if (window.location.pathname.endsWith("index.php") || window.location.pathname === "/" || window.location.pathname.endsWith("/")) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
      setTimeout(() => {
        window.location.reload();
      }, 100);
    }
  });
});

// TVMaze API search & favorites handling
const searchInput = document.getElementById("search-input");
const apiResults = document.getElementById("api-results");
const favoritesGrid = document.querySelector(".favorites-grid");
const loadMoreBtn = document.getElementById("load-more-btn");
const loadMoreWrapper = document.querySelector(".load-more-wrapper");

const API_BASE = "https://api.tvmaze.com";

// Store all fetched favorite movies
let allFavoriteMovies = [];
let displayedCount = 0;
const MOVIES_PER_PAGE = 3;

function createResultCard(item) {
  const show = item.show || item;
  const card = document.createElement("article");
  card.className = "card";

  const img = document.createElement("img");
  img.src =
    (show.image && (show.image.medium || show.image.original)) ||
    "https://via.placeholder.com/320x450?text=No+Image";
  img.alt = show.name || "Show poster";
  card.appendChild(img);

  const body = document.createElement("div");
  body.className = "card-body";
  const title = document.createElement("h3");
  title.textContent = show.name || "Untitled";
  const summary = document.createElement("p");
  summary.innerHTML =
    (show.summary &&
      show.summary.replace(/<[^>]+>/g, "").slice(0, 400) + "...") ||
    "No description available.";
  body.appendChild(title);
  body.appendChild(summary);
  card.appendChild(body);

  const footer = document.createElement("div");
  footer.className = "card-footer";
  const addBtn = document.createElement("button");
  addBtn.className = "btn primary-btn";
  addBtn.type = "button";
  addBtn.textContent = "Add to favourites";
  addBtn.addEventListener("click", () => addToFavorites(show));
  footer.appendChild(addBtn);
  card.appendChild(footer);

  return card;
}

function createSkeletonCard() {
  const card = document.createElement("article");
  card.className = "skeleton-card";

  const img = document.createElement("div");
  img.style.width = "100%";
  img.style.aspectRatio = "3 / 4";
  img.style.background = "linear-gradient(90deg, #2a2a2a 25%, #3a3a3a 50%, #2a2a2a 75%)";
  img.style.backgroundSize = "200% 100%";
  img.style.animation = "shimmer 1.5s infinite";
  card.appendChild(img);

  const body = document.createElement("div");
  body.className = "card-body";
  
  const skeletonTitle = document.createElement("div");
  skeletonTitle.className = "skeleton-title";
  body.appendChild(skeletonTitle);
  
  const skeletonText1 = document.createElement("div");
  skeletonText1.className = "skeleton-text";
  body.appendChild(skeletonText1);
  
  const skeletonText2 = document.createElement("div");
  skeletonText2.className = "skeleton-text";
  body.appendChild(skeletonText2);
  
  card.appendChild(body);

  return card;
}

function createFavoriteCard(show, delay = 0) {
  const card = document.createElement("article");
  card.className = "card";
  if (delay > 0) {
    card.style.animationDelay = `${delay}s`;
  }

  const closeBtn = document.createElement("button");
  closeBtn.className = "card-close";
  closeBtn.type = "button";
  closeBtn.setAttribute("aria-label", "Remove from favourites");
  closeBtn.textContent = "×";
  closeBtn.addEventListener("click", () => {
    card.style.animation = "fadeOut 0.3s ease-out";
    setTimeout(() => {
      card.remove();
    }, 300);
  });
  card.appendChild(closeBtn);

  const img = document.createElement("img");
  img.src =
    (show.image && (show.image.medium || show.image.original)) ||
    "https://via.placeholder.com/320x450?text=No+Image";
  img.alt = show.name || "Show poster";
  img.loading = "lazy";
  card.appendChild(img);

  const body = document.createElement("div");
  body.className = "card-body";
  const title = document.createElement("h3");
  title.textContent = show.name || "Untitled";
  const meta = document.createElement("p");
  const plainSummary = show.summary && show.summary.replace(/<[^>]+>/g, "");
  const text =
    (plainSummary && plainSummary.slice(0, 140) + "...") ||
    "No description available.";
  meta.textContent = text;
  body.appendChild(title);
  body.appendChild(meta);
  card.appendChild(body);

  return card;
}

async function loadInitialFavorites() {
  if (!favoritesGrid) return;

  // Show loading skeleton cards
  favoritesGrid.innerHTML = "";
  for (let i = 0; i < 3; i++) {
    const skeleton = createSkeletonCard();
    favoritesGrid.appendChild(skeleton);
  }

  // Fetch more movies (12 total)
  const searchTitles = [
    "Batman",
    "Wild Wild West",
    "Spiderman",
    "Superman",
    "Iron Man",
    "Avengers",
    "Wonder Woman",
    "Black Panther",
    "Thor",
    "Captain America",
    "Doctor Strange",
    "Guardians of the Galaxy",
  ];

  allFavoriteMovies = [];
  displayedCount = 0;

  // Fetch all movies
  for (const title of searchTitles) {
    try {
      const res = await fetch(
        `${API_BASE}/search/shows?q=${encodeURIComponent(title)}`
      );
      if (!res.ok) continue;
      const data = await res.json();
      if (Array.isArray(data) && data.length > 0) {
        const show = data[0].show || data[0];
        allFavoriteMovies.push(show);
      }
    } catch (err) {
      console.error("Error loading initial favourite", title, err);
    }
  }

  // Clear skeletons and display first 3
  favoritesGrid.innerHTML = "";
  displayNextMovies();
}

function displayNextMovies() {
  if (!favoritesGrid) return;

  const remaining = allFavoriteMovies.length - displayedCount;
  const toShow = Math.min(MOVIES_PER_PAGE, remaining);

  for (let i = 0; i < toShow; i++) {
    if (displayedCount < allFavoriteMovies.length) {
      const show = allFavoriteMovies[displayedCount];
      const delay = i * 0.1;
      const card = createFavoriteCard(show, delay);
      favoritesGrid.appendChild(card);
      displayedCount++;
    }
  }

  // Show/hide Load More button
  if (loadMoreWrapper) {
    if (displayedCount < allFavoriteMovies.length) {
      loadMoreWrapper.style.display = "flex";
      loadMoreWrapper.style.justifyContent = "center";
      loadMoreWrapper.style.marginTop = "2rem";
    } else {
      loadMoreWrapper.style.display = "none";
    }
  }
}

function addToFavorites(show) {
  if (!favoritesGrid) return;
  const card = createFavoriteCard(show);
  favoritesGrid.appendChild(card);
}

async function performSearch() {
  if (!favoritesGrid || !searchInput) return;
  const query = searchInput.value.trim();

  if (!query) {
    // Show default favorites when search is cleared
    favoritesGrid.innerHTML = "";
    favoritesGrid.style.display = "grid";
    if (loadMoreWrapper) {
      loadMoreWrapper.style.display = displayedCount < allFavoriteMovies.length ? "flex" : "none";
    }
    // Restore default favorites
    displayedCount = 0;
    displayNextMovies();
    return;
  }

  // Hide Load More button when searching
  if (loadMoreWrapper) {
    loadMoreWrapper.style.display = "none";
  }

  // Clear favorites grid and show loading skeleton
  favoritesGrid.innerHTML = "";
  favoritesGrid.style.display = "grid";
  
  // Show loading skeleton cards
  for (let i = 0; i < 3; i++) {
    const skeleton = createSkeletonCard();
    favoritesGrid.appendChild(skeleton);
  }

  try {
    const response = await fetch(
      `${API_BASE}/search/shows?q=${encodeURIComponent(query)}`
    );
    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    const data = await response.json();
    
    // Clear skeletons
    favoritesGrid.innerHTML = "";
    
    if (!Array.isArray(data) || data.length === 0) {
      const msg = document.createElement("p");
      msg.className = "api-empty";
      msg.textContent = "No results found. Try another title.";
      msg.style.gridColumn = "1 / -1";
      msg.style.textAlign = "center";
      msg.style.color = "#aaaaaa";
      favoritesGrid.appendChild(msg);
      return;
    }

    // Use same card style as favorites (with X button) and put in favorites grid
    data.slice(0, 12).forEach((item, index) => {
      const show = item.show || item;
      const delay = index * 0.1;
      const card = createFavoriteCard(show, delay);
      favoritesGrid.appendChild(card);
    });
  } catch (err) {
    console.error(err);
    const msg = document.createElement("p");
    msg.className = "api-empty";
    msg.textContent = "Something went wrong while fetching data.";
    msg.style.gridColumn = "1 / -1";
    msg.style.textAlign = "center";
    msg.style.color = "#aaaaaa";
    favoritesGrid.appendChild(msg);
  }
}

// Auto-search while typing (debounced)
let searchDebounceId;
if (searchInput) {
  searchInput.addEventListener("input", () => {
    if (searchDebounceId) {
      clearTimeout(searchDebounceId);
    }
    searchDebounceId = setTimeout(() => {
      performSearch();
    }, 400);
  });
}

// Load some initial favourites on page load
loadInitialFavorites();

// Load More button handler
if (loadMoreBtn) {
  loadMoreBtn.addEventListener("click", () => {
    displayNextMovies();
  });
}

// Frontend form validation
const contactForm = document.getElementById("contact-form");
const termsLink = document.getElementById("terms-link");
const termsModal = document.getElementById("terms-modal");

// Clear form inputs after successful submission
function clearForm() {
  if (!contactForm) return;
  
  // Clear all text inputs and textarea
  const inputs = contactForm.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea');
  inputs.forEach(input => {
    input.value = '';
    input.classList.remove("field-invalid");
  });
  
  // Uncheck checkbox
  const checkbox = contactForm.querySelector('input[type="checkbox"]');
  if (checkbox) {
    checkbox.checked = false;
    checkbox.classList.remove("field-invalid");
  }
  
  // Remove any error messages
  const errorMessages = contactForm.querySelectorAll('.field-error');
  errorMessages.forEach(error => error.remove());
}

// Check if form was successfully submitted and clear it
const successMessage = document.querySelector('.form-message.success');
if (successMessage && contactForm) {
  // Clear form after a short delay to show success message
  setTimeout(() => {
    clearForm();
  }, 100);
}

if (contactForm) {
  contactForm.addEventListener("submit", (event) => {
    const submitButton = contactForm.querySelector('button[type="submit"]');
    const requiredFields = [
      { id: "first_name", name: "First name" },
      { id: "last_name", name: "Last name" },
      { id: "email", name: "Email", type: "email" },
      { id: "comments", name: "Comments" },
      { id: "policy", name: "Terms & Conditions", type: "checkbox" },
    ];

    let hasError = false;

    requiredFields.forEach((field) => {
      const el = document.getElementById(field.id);
      if (!el) return;
      el.classList.remove("field-invalid");

      if (field.type === "checkbox") {
        if (!el.checked) {
          hasError = true;
          el.classList.add("field-invalid");
        }
        return;
      }

      const value = el.value.trim();

      if (!value) {
        hasError = true;
        el.classList.add("field-invalid");
        return;
      }

      if (field.type === "email") {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
          hasError = true;
          el.classList.add("field-invalid");
        }
      }
    });

    if (hasError) {
      event.preventDefault();
      alert("Please fill in all required fields with valid information.");
      return;
    }

    // Show loading state on button
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.classList.add("btn-loading");
      const originalText = submitButton.textContent;
      submitButton.setAttribute("data-original-text", originalText);
      submitButton.textContent = "Sending...";
      
      // Prevent double submission
      contactForm.style.pointerEvents = "none";
      contactForm.style.opacity = "0.7";
    }

    // Form will submit normally - PHP will handle it
    // If form submission fails, we'll need to handle it via AJAX
    // For now, the page will reload on success/error
  });
}

if (termsLink && termsModal) {
  const closeBtn = termsModal.querySelector(".modal-close");

  const openModal = () => {
    termsModal.classList.add("modal-open");
    termsModal.setAttribute("aria-hidden", "false");
  };

  const closeModal = () => {
    termsModal.classList.remove("modal-open");
    termsModal.setAttribute("aria-hidden", "true");
  };

  termsLink.addEventListener("click", (event) => {
    event.preventDefault();
    openModal();
  });

  if (closeBtn) {
    closeBtn.addEventListener("click", closeModal);
  }

  termsModal.addEventListener("click", (event) => {
    if (event.target === termsModal) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeModal();
    }
  });
}
