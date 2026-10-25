
<?php
session_start();
require_once "db.php";

if (!isset($_GET['movie_id']) || !ctype_digit($_GET['movie_id'])) {
    header("Location: index.php");
    exit;
}

$movie_id = (int) $_GET['movie_id'];

$stmt = $conn->prepare(
    "SELECT movie_id, title, language, genre, duration
     FROM movies
     WHERE movie_id = ?"
);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) {
    die("Movie not found. Please return to the movies page.");
}

$stmt = $conn->prepare(
    "SELECT DISTINCT t.theatre_id, t.theatre_name,
                    t.place, t.address
     FROM theatres t
     JOIN show_table st ON st.theatre_id = t.theatre_id
     WHERE st.movie_id = ?
       AND TIMESTAMP(st.show_date, st.show_time) >= NOW()
     ORDER BY t.place, t.theatre_name"
);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

$theatres = [];
$places = [];

while ($row = $result->fetch_assoc()) {
    $theatres[] = $row;

    if (!in_array($row['place'], $places, true)) {
        $places[] = $row['place'];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Select Theatre | CineBook</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #8b5cf6;
    --primary-dark: #6d28d9;
    --lavender: #ede7ff;
    --background: #f8f5ff;
    --text: #30234a;
    --muted: #766b89;
    --border: #e6def5;
    --white: #ffffff;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Poppins', sans-serif;
    background: var(--background);
    color: var(--text);
}

.navbar {
    min-height: 78px;
    padding: 16px 7%;
    background: var(--white);
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.logo {
    color: var(--primary-dark);
    font-size: 27px;
    font-weight: 800;
    text-decoration: none;
    letter-spacing: -1px;
}

.logo span {
    color: var(--primary);
}

.nav-link {
    color: var(--primary-dark);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.nav-link:hover {
    color: var(--primary);
}

.page {
    width: min(1050px, 90%);
    margin: 42px auto;
}

.page-heading {
    margin-bottom: 25px;
}

.eyebrow {
    display: inline-block;
    padding: 7px 13px;
    border-radius: 20px;
    background: var(--lavender);
    color: var(--primary-dark);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.page-heading h1 {
    margin: 14px 0 8px;
    font-size: clamp(27px, 4vw, 37px);
    line-height: 1.3;
    letter-spacing: -1px;
}

.page-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.8;
}

.booking-card {
    display: grid;
    grid-template-columns: 0.85fr 1.15fr;
    overflow: hidden;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 22px;
    box-shadow: 0 12px 40px rgba(72, 42, 120, 0.07);
}

.movie-panel {
    padding: 34px;
    background: linear-gradient(145deg, #eee7ff, #faf8ff);
    border-right: 1px solid var(--border);
}

.movie-icon {
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: var(--white);
    color: var(--primary-dark);
    font-size: 30px;
    box-shadow: 0 5px 18px rgba(72, 42, 120, 0.08);
}

.movie-panel h2 {
    margin: 22px 0 10px;
    font-size: 25px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

.movie-description {
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

.movie-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 20px;
}

.movie-tag {
    padding: 7px 11px;
    border: 1px solid #ded3f8;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.8);
    color: #5b3b91;
    font-size: 11px;
    font-weight: 600;
}

.selection-panel {
    padding: 34px;
}

.selection-panel h2 {
    margin: 0 0 7px;
    font-size: 22px;
}

.section-description {
    margin: 0 0 25px;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

label {
    display: block;
    margin: 20px 0 9px;
    font-size: 13px;
    font-weight: 600;
}

.select-wrap {
    position: relative;
}

select {
    width: 100%;
    min-height: 52px;
    padding: 13px 14px;
    border: 1px solid var(--border);
    border-radius: 11px;
    outline: none;
    background: var(--white);
    color: var(--text);
    font-family: inherit;
    font-size: 13px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12);
}

select:disabled {
    background: #f6f3fb;
    color: #a49aaf;
    cursor: not-allowed;
}

.theatre-info {
    margin-top: 14px;
    padding: 13px 14px;
    border-radius: 11px;
    background: #faf8ff;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.8;
    overflow-wrap: anywhere;
}

.theatre-info strong {
    color: var(--text);
}

.primary-button {
    display: block;
    width: 100%;
    min-height: 52px;
    margin-top: 25px;
    padding: 14px 18px;
    border: none;
    border-radius: 11px;
    background: var(--primary);
    color: var(--white);
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}

.primary-button:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.primary-button:disabled {
    background: #c8b8ed;
    cursor: not-allowed;
    transform: none;
}

.notice {
    padding: 20px;
    border: 1px solid #e5d8ff;
    border-radius: 13px;
    background: #faf7ff;
    color: #5b3b91;
    font-size: 13px;
    line-height: 1.9;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 25px;
    color: var(--primary-dark);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.back-link:hover {
    color: var(--primary);
}

.footer {
    padding: 25px 15px;
    color: #9388a4;
    text-align: center;
    font-size: 12px;
}

@media (max-width: 700px) {
    .navbar {
        padding: 16px 5%;
    }

    .logo {
        font-size: 23px;
    }

    .page {
        width: 92%;
        margin: 28px auto;
    }

    .booking-card {
        grid-template-columns: 1fr;
    }

    .movie-panel {
        padding: 25px;
        border-right: none;
        border-bottom: 1px solid var(--border);
    }

    .selection-panel {
        padding: 25px;
    }

    .movie-panel h2 {
        margin-top: 15px;
    }
}
</style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="index.php">Cine<span>Book</span></a>
    <a class="nav-link" href="index.php">← Back to Movies</a>
</header>

<main class="page">

    <section class="page-heading">
        <span class="eyebrow">Your movie, your choice</span>
        <h1>Choose Your Theatre</h1>
        <p>Select a location and theatre to discover available showtimes.</p>
    </section>

    <section class="booking-card">

        <div class="movie-panel">
            <div class="movie-icon">🎬</div>

            <h2>
                <?php echo htmlspecialchars($movie['title']); ?>
            </h2>

            <p class="movie-description">
                You have selected this movie. Choose your preferred theatre
                to continue your ticket booking.
            </p>

            <div class="movie-tags">
                <span class="movie-tag">
                    <?php echo htmlspecialchars($movie['genre']); ?>
                </span>

                <span class="movie-tag">
                    <?php echo htmlspecialchars($movie['language']); ?>
                </span>

                <span class="movie-tag">
                    <?php echo (int) $movie['duration']; ?> minutes
                </span>
            </div>
        </div>

        <div class="selection-panel">
            <h2>Select a location</h2>
            <p class="section-description">
                Find a theatre near your preferred place and continue to
                the available showtimes.
            </p>

            <?php if (count($theatres) > 0): ?>

                <form action="showtimes.php" method="GET">
                    <input type="hidden" name="movie_id"
                           value="<?php echo $movie_id; ?>">

                    <label for="place">Preferred Place</label>
                    <div class="select-wrap">
                        <select id="place" required>
                            <option value="">Choose a place</option>

                            <?php foreach ($places as $place): ?>
                                <option value="<?php echo htmlspecialchars($place, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($place); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label for="theatre_id">Available Theatre</label>
                    <div class="select-wrap">
                        <select name="theatre_id" id="theatre_id" required disabled>
                            <option value="">Choose a place first</option>

                            <?php foreach ($theatres as $theatre): ?>
                                <option
                                    value="<?php echo (int) $theatre['theatre_id']; ?>"
                                    data-place="<?php echo htmlspecialchars($theatre['place'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-address="<?php echo htmlspecialchars($theatre['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php
                                    echo htmlspecialchars($theatre['theatre_name'])
                                        . " - " . htmlspecialchars($theatre['place']);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="theatre-info" id="theatre-info">
                        <strong>Location details</strong><br>
                        Select a place and theatre to view the address.
                    </div>

                    <button class="primary-button" type="submit" id="continue-button" disabled>
                        View Showtimes →
                    </button>
                </form>

            <?php else: ?>

                <div class="notice">
                    <strong>No upcoming shows available</strong><br>
                    There are currently no future showtimes for this movie.
                    Please return to the movies page and select another movie,
                    or add a future showtime to the database.
                </div>

            <?php endif; ?>

            <a class="back-link" href="index.php">← Return to all movies</a>
        </div>

    </section>

</main>

<footer class="footer">
    © <?php echo date('Y'); ?> CineBook. Your next movie experience starts here.
</footer>

<script>
const placeSelect = document.getElementById("place");
const theatreSelect = document.getElementById("theatre_id");
const theatreInfo = document.getElementById("theatre-info");
const continueButton = document.getElementById("continue-button");

if (placeSelect && theatreSelect) {
    const allTheatreOptions = Array.from(theatreSelect.options)
        .filter(option => option.value !== "");

    placeSelect.addEventListener("change", function () {
        const selectedPlace = this.value;

        theatreSelect.innerHTML = "";

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = selectedPlace
            ? "Choose a theatre"
            : "Choose a place first";

        theatreSelect.appendChild(placeholder);

        allTheatreOptions.forEach(function (option) {
            if (option.dataset.place === selectedPlace) {
                theatreSelect.appendChild(option.cloneNode(true));
            }
        });

        theatreSelect.disabled = !selectedPlace;
        continueButton.disabled = true;

        theatreInfo.innerHTML = selectedPlace
            ? "<strong>Location details</strong><br>Select a theatre to view its address."
            : "<strong>Location details</strong><br>Select a place and theatre to view the address.";
    });

    theatreSelect.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];

        if (this.value && selectedOption) {
            const address = selectedOption.dataset.address;

            theatreInfo.innerHTML = "";

            const heading = document.createElement("strong");
            heading.textContent = "Theatre address";

            const addressText = document.createElement("div");
            addressText.textContent = address || "Address not available";

            theatreInfo.appendChild(heading);
            theatreInfo.appendChild(addressText);

            continueButton.disabled = false;
        } else {
            theatreInfo.textContent = "Select a theatre to view its address.";
            continueButton.disabled = true;
        }
    });
}
</script>

</body>
</html>
