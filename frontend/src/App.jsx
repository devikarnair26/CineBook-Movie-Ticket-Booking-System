
import { useEffect, useState } from "react";
import "./App.css";

const API_URL = "http://localhost/movie_booking";

function App() {
  const [movies, setMovies] = useState([]);
  const [moviesLoading, setMoviesLoading] = useState(true);
  const [moviesError, setMoviesError] = useState("");
  const [searchTerm, setSearchTerm] = useState("");
  const [selectedGenre, setSelectedGenre] = useState("All");

  useEffect(() => {
    async function fetchMovies() {
      try {
        setMoviesLoading(true);
        setMoviesError("");

        const response = await fetch(`${API_URL}/movies_api.php`);
        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(data.message || "Unable to load movies.");
        }

        setMovies(data.movies || []);
      } catch (error) {
        setMoviesError(
          error.message || "Unable to connect to the movie database."
        );
      } finally {
        setMoviesLoading(false);
      }
    }

    fetchMovies();
  }, []);

  const genres = [
    "All",
    ...new Set(
      movies.map((movie) => movie.genre).filter(Boolean)
    ),
  ];

  const filteredMovies = movies.filter((movie) => {
    const search = searchTerm.toLowerCase();

    const matchesSearch = [
      movie.title,
      movie.genre,
      movie.language,
    ]
      .filter(Boolean)
      .some((value) => value.toLowerCase().includes(search));

    const matchesGenre =
      selectedGenre === "All" ||
      movie.genre === selectedGenre;

    return matchesSearch && matchesGenre;
  });

  function handleBookTickets(movie) {
    sessionStorage.setItem(
      "selectedMovie",
      JSON.stringify(movie)
    );

    window.location.href =
      `${API_URL}/theater_selection.php?movie_id=${encodeURIComponent(
        movie.movie_id
      )}`;
  }

  return (
    <div className="app">
      <header className="app-header">
        <a className="brand" href="#home">
          <span className="brand-icon">🎬</span>
          <span>CineBook</span>
        </a>

        <nav className="main-nav">
          <a href="#home">Home</a>
          <a href="#movies">Movies</a>
          <a href="#about">About</a>
        </nav>

        <a
          className="login-link"
          href={`${API_URL}/login.php`}
        >
          Login
        </a>
      </header>

      <main>
        <section className="hero" id="home">
          <div className="hero-content">
            <span className="hero-tag">
              YOUR CINEMA EXPERIENCE
            </span>

            <h1>
              Your next great
              <br />
              <span>movie night</span> starts here.
            </h1>

            <p>
              Discover movies, explore genres, and get ready
              for your next big-screen experience.
            </p>

            <a className="hero-button" href="#movies">
              Explore Movies <span>→</span>
            </a>
          </div>

          <div className="hero-art" aria-hidden="true">
            <div className="hero-circle">
              <span>▶</span>
            </div>
            <div className="hero-small-card">
              NOW SHOWING
            </div>
            <div className="hero-decoration">CINEMA</div>
          </div>
        </section>

        <section className="movies-section" id="movies">
          <div className="section-heading">
            <div>
              <span className="section-tag">
                THE BIG SCREEN
              </span>
              <h2>Explore Movies</h2>
              <p>
                Find a movie for your next cinema visit.
              </p>
            </div>

            <span className="movie-count">
              {movies.length} movies
            </span>
          </div>

          <div className="movie-controls">
            <input
              type="search"
              placeholder="Search movies, genres, languages..."
              value={searchTerm}
              onChange={(event) =>
                setSearchTerm(event.target.value)
              }
              aria-label="Search movies"
            />

            <select
              value={selectedGenre}
              onChange={(event) =>
                setSelectedGenre(event.target.value)
              }
              aria-label="Filter by genre"
            >
              {genres.map((genre) => (
                <option value={genre} key={genre}>
                  {genre === "All" ? "All Genres" : genre}
                </option>
              ))}
            </select>
          </div>

          {moviesLoading ? (
            <div className="movie-message">
              <div className="loading-spinner"></div>
              <p>Loading movies from the database...</p>
            </div>
          ) : moviesError ? (
            <div className="movie-message error-message">
              <h3>Unable to load movies</h3>
              <p>{moviesError}</p>
              <button
                onClick={() => window.location.reload()}
              >
                Try Again
              </button>
            </div>
          ) : filteredMovies.length === 0 ? (
            <div className="movie-message">
              <h3>No movies found</h3>
              <p>
                Try another search term or genre.
              </p>
            </div>
          ) : (
            <div className="movie-grid">
              {filteredMovies.map((movie) => (
                <article
                  className="movie-card"
                  key={movie.movie_id}
                >
                  <div className="movie-poster">
                    {movie.poster_url ? (
                      <img
                        src={movie.poster_url}
                        alt={`${movie.title} poster`}
                        loading="lazy"
                      />
                    ) : (
                      <div className="poster-placeholder">
                        <span className="poster-icon">
                          🎬
                        </span>
                        <span className="poster-title">
                          {movie.title}
                        </span>
                        <span className="poster-label">
                          NOW SHOWING
                        </span>
                      </div>
                    )}

                    <span className="genre-badge">
                      {movie.genre || "Movie"}
                    </span>
                  </div>

                  <div className="movie-info">
                    <h3>{movie.title}</h3>

                    <div className="movie-meta">
                      <span>
                        {movie.language || "Language N/A"}
                      </span>

                      {movie.duration && (
                        <>
                          <span className="meta-dot">•</span>
                          <span>
                            {movie.duration} min
                          </span>
                        </>
                      )}
                    </div>

                    {movie.release_date && (
                      <p className="release-date">
                        Release date: {movie.release_date}
                      </p>
                    )}

                    <p className="movie-description">
                      {movie.description ||
                        "No description available."}
                    </p>

                    <button
                      className="book-button"
                      onClick={() =>
                        handleBookTickets(movie)
                      }
                    >
                      Book Tickets <span>→</span>
                    </button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </section>

        <section className="about-section" id="about">
          <span className="section-tag">
            ABOUT CINEBOOK
          </span>
          <h2>Your movie. Your moment.</h2>
          <p>
            Discover movies and start your ticket-booking
            journey in one place with CineBook.
          </p>
        </section>
      </main>

      <footer className="app-footer">
        <a className="brand footer-brand" href="#home">
          <span className="brand-icon">🎬</span>
          <span>CineBook</span>
        </a>

        <p>Discover movies. Make memories.</p>

        <span>
          © {new Date().getFullYear()} CineBook
        </span>
      </footer>
    </div>
  );
}

export default App;
