
<?php
require_once "db.php";

$movieId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$movieId) {
    exit("Invalid movie ID. <a href='index.php'>Go Home</a>");
}

// Fetch movie details from movies table
$stmt = $conn->prepare(
    "SELECT * FROM movies WHERE movie_id = ?"
);

$stmt->bind_param("i", $movieId);
$stmt->execute();

$result = $stmt->get_result();
$movie = $result->fetch_assoc();

$stmt->close();

if (!$movie) {
    exit("Movie not found. <a href='index.php'>Go Home</a>");
}

// Fetch upcoming shows
$stmt = $conn->prepare(
    "SELECT st.show_id, st.show_date, st.show_time,
            st.ticket_price,
            sc.screen_name, th.theatre_name, th.location
     FROM show_table st
     JOIN screen sc ON st.screen_id = sc.screen_id
     JOIN theatre th ON sc.theatre_id = th.theatre_id
     WHERE st.movie_id = ?
       AND TIMESTAMP(st.show_date, st.show_time) >= NOW()
     ORDER BY st.show_date, st.show_time"
);

$stmt->bind_param("i", $movieId);
$stmt->execute();

$shows = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie["title"]) ?> | CineBook</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">CineBook</a>
    <a href="my_bookings.php">My Bookings</a>
</header>

<main class="page-content">

    <h1><?= htmlspecialchars($movie["title"]) ?></h1>

    <p>
        <?= htmlspecialchars($movie["genre"] ?? "") ?> |
        <?= htmlspecialchars($movie["language"] ?? "") ?> |
        <?= (int)$movie["duration"] ?> minutes
    </p>

    <p>
        <?= nl2br(htmlspecialchars($movie["description"] ?? "")) ?>
    </p>

    <h2>Choose a Showtime</h2>

    <?php if ($shows->num_rows > 0): ?>

        <div class="show-list">

            <?php while ($show = $shows->fetch_assoc()): ?>

                <article class="show-card">

                    <h3>
                        <?= htmlspecialchars($show["theatre_name"]) ?>
                    </h3>

                    <p>
                        Location:
                        <?= htmlspecialchars($show["location"]) ?>
                    </p>

                    <p>
                        Screen:
                        <?= htmlspecialchars($show["screen_name"]) ?>
                    </p>

                    <p>
                        Date:
                        <?= htmlspecialchars($show["show_date"]) ?>
                    </p>

                    <p>
                        Time:
                        <?= htmlspecialchars($show["show_time"]) ?>
                    </p>

                    <p>
                        Ticket Price: ₹<?= number_format((float)$show["ticket_price"], 2) ?>
                    </p>

                    <a class="btn"
                       href="seats.php?show_id=<?= (int)$show["show_id"] ?>">
                        Select Seats
                    </a>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <p>No upcoming shows are available for this movie.</p>

    <?php endif; ?>

    <a href="index.php">Back to Movies</a>

</main>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
