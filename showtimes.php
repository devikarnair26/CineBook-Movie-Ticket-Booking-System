
<?php
session_start();
require_once "db.php";

if (
    !isset($_GET["movie_id"], $_GET["theatre_id"]) ||
    !ctype_digit((string) $_GET["movie_id"]) ||
    !ctype_digit((string) $_GET["theatre_id"])
) {
    header("Location: index.php");
    exit;
}

$movie_id = (int) $_GET["movie_id"];
$theatre_id = (int) $_GET["theatre_id"];

// Get movie and theatre information.
$stmt = $conn->prepare(
    "SELECT
        m.movie_id,
        m.title,
        m.genre,
        m.language,
        m.duration,
        t.theatre_id,
        t.theatre_name,
        t.place,
        t.address
     FROM movies AS m
     CROSS JOIN theatres AS t
     WHERE m.movie_id = ?
       AND t.theatre_id = ?"
);

$stmt->bind_param("ii", $movie_id, $theatre_id);
$stmt->execute();
$details = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$details) {
    http_response_code(404);
    exit("Movie or theatre not found. Please return and select again.");
}

// Get future shows for the selected movie and theatre.
$stmt = $conn->prepare(
    "SELECT
        show_id,
        show_date,
        show_time,
        screen_id,
        ticket_price
     FROM show_table
     WHERE movie_id = ?
       AND theatre_id = ?
       AND TIMESTAMP(show_date, show_time) >= NOW()
     ORDER BY show_date ASC, show_time ASC"
);

$stmt->bind_param("ii", $movie_id, $theatre_id);
$stmt->execute();
$shows = $stmt->get_result();

$show_list = [];

while ($show = $shows->fetch_assoc()) {
    $show_list[] = $show;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose a Showtime | CineBook</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f3ff;
            color: #292044;
        }

        header {
            background: #6d28d9;
            color: white;
            padding: 22px;
            text-align: center;
        }

        header h1 {
            margin: 0 0 10px;
        }

        header p {
            margin: 0;
        }

        .container {
            max-width: 850px;
            margin: 35px auto;
            padding: 0 20px 30px;
        }

        .movie-info,
        .show-card,
        .notice {
            background: white;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 5px 18px #28134a12;
            margin-bottom: 18px;
        }

        .movie-info h2 {
            color: #5b21b6;
            margin-top: 0;
        }

        .show-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
        }

        .show-card p {
            margin: 7px 0;
        }

        .price {
            font-weight: bold;
            color: #5b21b6;
        }

        .button {
            display: inline-block;
            background: #6d28d9;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .button:hover {
            background: #5b21b6;
        }

        .back {
            display: inline-block;
            margin: 12px 12px 0 0;
            color: #6d28d9;
            text-decoration: none;
        }

        .notice {
            line-height: 1.7;
        }

        .notice strong {
            color: #5b21b6;
        }

        @media (max-width: 550px) {
            .show-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
<header>
    <h1>Choose a Showtime</h1>
    <p>Select a convenient time for your movie</p>
</header>

<main class="container">

    <section class="movie-info">
        <h2><?= htmlspecialchars($details["title"]) ?></h2>

        <p>
            <?= htmlspecialchars($details["genre"] ?? "Genre not specified") ?>
            |
            <?= htmlspecialchars($details["language"] ?? "Language not specified") ?>
            |
            <?= (int) $details["duration"] ?> minutes
        </p>

        <p>
            <strong>Theatre:</strong>
            <?= htmlspecialchars($details["theatre_name"]) ?>
        </p>

        <p>
            <strong>Place:</strong>
            <?= htmlspecialchars($details["place"]) ?>
        </p>

        <?php if (!empty($details["address"])): ?>
            <p>
                <strong>Address:</strong>
                <?= htmlspecialchars($details["address"]) ?>
            </p>
        <?php endif; ?>
    </section>

    <?php if (count($show_list) > 0): ?>

        <?php foreach ($show_list as $show): ?>
            <section class="show-card">
                <div>
                    <p>
                        <strong>Date:</strong>
                        <?= date("d M Y", strtotime($show["show_date"])) ?>
                    </p>

                    <p>
                        <strong>Time:</strong>
                        <?= date("h:i A", strtotime($show["show_time"])) ?>
                    </p>

                    <p>
                        <strong>Screen:</strong>
                        <?= (int) $show["screen_id"] ?>
                    </p>

                    <p class="price">
                        ₹<?= number_format((float) $show["ticket_price"], 2) ?>
                        per seat
                    </p>
                </div>

                <a
                    class="button"
                    href="seats.php?show_id=<?= (int) $show["show_id"] ?>"
                >
                    Select Seats
                </a>
            </section>
        <?php endforeach; ?>

    <?php else: ?>

        <div class="notice">
            <strong>No upcoming shows are available for this movie at this theatre.</strong>

            <p>
                The selected movie and theatre have no matching future
                showtime records. Please select another theatre or check
                the showtime records in your database.
            </p>
        </div>

    <?php endif; ?>

    <a
        class="back"
        href="theater_selection.php?movie_id=<?= $movie_id ?>"
    >
        ← Back to Theatre Selection
    </a>

    <a class="back" href="index.php">
        ← Back to Movies
    </a>

</main>
</body>
</html>
