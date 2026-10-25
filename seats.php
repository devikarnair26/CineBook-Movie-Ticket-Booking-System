
<?php
session_start();
require_once "db.php";

if (!isset($_GET['show_id']) || !ctype_digit($_GET['show_id'])) {
    exit("Invalid show. Please select a movie first.");
}

$show_id = (int) $_GET['show_id'];

$sql = "SELECT sh.show_id, sh.screen_id, sh.show_date,
               sh.show_time, sh.ticket_price, m.title
        FROM show_table sh
        JOIN movies m ON m.movie_id = sh.movie_id
        WHERE sh.show_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $show_id);
$stmt->execute();
$show = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$show) {
    exit("Show not found.");
}

$sql = "SELECT s.seat_id, s.seat_number, s.seat_type,
        EXISTS (
            SELECT 1
            FROM booking_seat bs
            JOIN bookings b ON b.booking_id = bs.booking_id
            WHERE bs.seat_id = s.seat_id
              AND b.show_id = ?
              AND b.status = 'Confirmed'
        ) AS is_booked
        FROM seat s
        WHERE s.screen_id = ?
        ORDER BY
            LEFT(s.seat_number, 1),
            CAST(SUBSTRING(s.seat_number, 2) AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $show_id, $show['screen_id']);
$stmt->execute();
$result = $stmt->get_result();

$seats = [];
while ($row = $result->fetch_assoc()) {
    $seats[] = $row;
}
$stmt->close();

$seatCount = count($seats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Select Seats | CineBook</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #8b5cf6;
    --primary-dark: #6d28d9;
    --background: #f8f5ff;
    --lavender: #ede7ff;
    --text: #30234a;
    --muted: #81758f;
    --border: #e8e0f5;
    --white: #ffffff;
    --available: #dcfce7;
    --available-text: #166534;
    --selected: #8b5cf6;
    --booked: #fee2e2;
    --booked-text: #991b1b;
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
    min-height: 76px;
    padding: 15px 7%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    background: var(--white);
    border-bottom: 1px solid var(--border);
}

.logo {
    color: var(--primary-dark);
    font-size: 27px;
    font-weight: 800;
    letter-spacing: -1px;
    text-decoration: none;
}

.logo span {
    color: var(--primary);
}

.nav-link {
    color: var(--primary-dark);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.nav-link:hover,
.back-link:hover {
    color: var(--primary);
}

.page {
    width: min(1050px, 92%);
    margin: 35px auto;
}

.page-heading {
    margin-bottom: 23px;
}

.eyebrow {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 30px;
    background: var(--lavender);
    color: var(--primary-dark);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.page-heading h1 {
    margin: 12px 0 5px;
    font-size: clamp(27px, 4vw, 36px);
    line-height: 1.35;
    letter-spacing: -1px;
}

.page-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

.layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 22px;
    align-items: start;
}

.card {
    padding: 25px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 20px;
    box-shadow: 0 10px 35px rgba(72, 42, 120, 0.06);
}

.movie-details {
    display: flex;
    align-items: center;
    gap: 15px;
    padding-bottom: 21px;
    border-bottom: 1px solid var(--border);
}

.movie-icon {
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--lavender);
    border-radius: 15px;
    font-size: 26px;
}

.movie-details h2 {
    margin: 0 0 5px;
    font-size: 21px;
    overflow-wrap: anywhere;
}

.movie-details p {
    margin: 3px 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.7;
}

.seat-heading {
    margin: 25px 0 6px;
    font-size: 17px;
}

.seat-subtitle {
    margin: 0;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.7;
}

.screen-area {
    margin: 30px auto 24px;
    max-width: 530px;
    text-align: center;
}

.screen {
    height: 10px;
    margin: 0 12px 13px;
    border-radius: 50% 50% 5px 5px;
    background: linear-gradient(90deg, #c4b5fd, #8b5cf6, #c4b5fd);
    box-shadow: 0 8px 18px rgba(139, 92, 246, 0.2);
}

.screen-label {
    color: var(--muted);
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 3px;
}

.legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
    margin: 24px 0;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--muted);
    font-size: 11px;
}

.dot {
    width: 13px;
    height: 13px;
    border-radius: 4px;
}

.available-dot {
    background: var(--available);
    border: 1px solid #86efac;
}

.selected-dot {
    background: var(--selected);
}

.booked-dot {
    background: var(--booked);
    border: 1px solid #fca5a5;
}

.seat-grid {
    display: grid;
    grid-template-columns: repeat(10, minmax(0, 1fr));
    gap: 9px;
    max-width: 560px;
    margin: 0 auto;
}

.seat {
    min-width: 0;
    aspect-ratio: 1.15 / 1;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 8px 8px 5px 5px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 500;
    transition: background 0.18s, transform 0.18s, color 0.18s;
}

.seat.available {
    border: 1px solid #bbf7d0;
    background: var(--available);
    color: var(--available-text);
    cursor: pointer;
}

.seat.available:hover {
    background: #bbf7d0;
    transform: translateY(-2px);
}

.seat input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.seat.available:has(input:checked) {
    background: var(--selected);
    border-color: var(--selected);
    color: var(--white);
    box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
}

.seat.available:has(input:focus-visible) {
    outline: 3px solid #c4b5fd;
    outline-offset: 2px;
}

.seat.booked {
    border: 1px solid #fecaca;
    background: var(--booked);
    color: var(--booked-text);
    cursor: not-allowed;
}

.empty-seats {
    padding: 22px 10px;
    color: var(--muted);
    font-size: 13px;
    text-align: center;
    line-height: 1.8;
}

.side-card {
    position: sticky;
    top: 20px;
}

.side-card h2 {
    margin: 0 0 20px;
    font-size: 18px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin: 15px 0;
    font-size: 12px;
}

.summary-label {
    color: var(--muted);
}

.summary-value {
    font-weight: 600;
    text-align: right;
    overflow-wrap: anywhere;
}

.summary-divider {
    height: 1px;
    margin: 20px 0;
    background: var(--border);
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

.total-row span {
    font-size: 13px;
    font-weight: 600;
}

.total-row strong {
    color: var(--primary-dark);
    font-size: 22px;
    overflow-wrap: anywhere;
}

.helper {
    margin: 13px 0 0;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.8;
}

.primary-button {
    width: 100%;
    min-height: 49px;
    margin-top: 22px;
    padding: 12px;
    border: none;
    border-radius: 11px;
    background: var(--primary);
    color: var(--white);
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.2s;
}

.primary-button:not(:disabled):hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.primary-button:disabled {
    background: #c9c0d9;
    color: #ffffff;
    cursor: not-allowed;
}

.back-link {
    display: inline-block;
    margin-top: 23px;
    color: var(--primary-dark);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}

.footer {
    padding: 22px 15px;
    color: #9489a4;
    font-size: 11px;
    text-align: center;
}

@media (max-width: 760px) {
    .layout {
        grid-template-columns: 1fr;
    }

    .side-card {
        position: static;
    }

    .page {
        margin-top: 25px;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 14px 5%;
    }

    .logo {
        font-size: 23px;
    }

    .nav-link {
        font-size: 11px;
    }

    .card {
        padding: 17px 12px;
        border-radius: 15px;
    }

    .movie-details {
        gap: 10px;
    }

    .movie-icon {
        width: 46px;
        height: 46px;
        font-size: 22px;
    }

    .movie-details h2 {
        font-size: 18px;
    }

    .seat-grid {
        grid-template-columns: repeat(10, minmax(0, 1fr));
        gap: 4px;
    }

    .seat {
        border-radius: 5px;
        font-size: 9px;
    }

    .legend {
        gap: 10px;
    }

    .legend-item {
        font-size: 10px;
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
        <span class="eyebrow">Your movie experience</span>
        <h1>Select Your Seats</h1>
        <p>Choose your favourite seats and get ready for the show.</p>
    </section>

    <div class="layout">

        <section class="card">

            <div class="movie-details">
                <div class="movie-icon">🎬</div>

                <div>
                    <h2><?php echo htmlspecialchars($show['title']); ?></h2>
                    <p>Show date:
                        <?php
                        echo date(
                            'd M Y',
                            strtotime($show['show_date'])
                        );
                        ?>
                    </p>
                    <p>Showtime:
                        <?php
                        echo date(
                            'h:i A',
                            strtotime($show['show_time'])
                        );
                        ?>
                    </p>
                </div>
            </div>

            <h2 class="seat-heading">Choose your seats</h2>
            <p class="seat-subtitle">
                Select one or more available seats from the layout below.
            </p>

            <div class="screen-area">
                <div class="screen"></div>
                <div class="screen-label">SCREEN THIS WAY</div>
            </div>

            <div class="legend">
                <span class="legend-item">
                    <i class="dot available-dot"></i> Available
                </span>
                <span class="legend-item">
                    <i class="dot selected-dot"></i> Selected
                </span>
                <span class="legend-item">
                    <i class="dot booked-dot"></i> Booked
                </span>
            </div>

            <?php if ($seatCount > 0): ?>

                <form action="book.php" method="POST" id="bookingForm">
                    <input type="hidden" name="show_id"
                           value="<?php echo $show_id; ?>">

                    <div class="seat-grid">
                        <?php foreach ($seats as $seat): ?>

                            <?php if ($seat['is_booked']): ?>

                                <span class="seat booked"
                                      title="Already booked">
                                    <?php
                                    echo htmlspecialchars(
                                        $seat['seat_number']
                                    );
                                    ?>
                                </span>

                            <?php else: ?>

                                <label class="seat available"
                                       title="Select seat <?php echo htmlspecialchars($seat['seat_number'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input
                                        type="checkbox"
                                        name="seat_ids[]"
                                        value="<?php echo (int)$seat['seat_id']; ?>"
                                        data-price="<?php echo (float)$show['ticket_price']; ?>"
                                        aria-label="Seat <?php echo htmlspecialchars($seat['seat_number'], ENT_QUOTES, 'UTF-8'); ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $seat['seat_number']
                                    );
                                    ?>
                                </label>

                            <?php endif; ?>

                        <?php endforeach; ?>
                    </div>

                    <p class="seat-subtitle" style="text-align:center; margin-top:18px;">
                        All prices are displayed in Indian Rupees (INR).
                    </p>

            <?php else: ?>

                <div class="empty-seats">
                    No seats are configured for this screen yet.
                    Please contact the administrator.
                </div>

            <?php endif; ?>

        </section>

        <aside class="card side-card">
            <h2>Booking Summary</h2>

            <div class="summary-row">
                <span class="summary-label">Movie</span>
                <span class="summary-value">
                    <?php echo htmlspecialchars($show['title']); ?>
                </span>
            </div>

            <div class="summary-row">
                <span class="summary-label">Show date</span>
                <span class="summary-value">
                    <?php
                    echo date(
                        'd M Y',
                        strtotime($show['show_date'])
                    );
                    ?>
                </span>
            </div>

            <div class="summary-row">
                <span class="summary-label">Showtime</span>
                <span class="summary-value">
                    <?php
                    echo date(
                        'h:i A',
                        strtotime($show['show_time'])
                    );
                    ?>
                </span>
            </div>

            <div class="summary-row">
                <span class="summary-label">Price per seat</span>
                <span class="summary-value">
                    ₹<?php echo number_format((float)$show['ticket_price'], 2); ?>
                </span>
            </div>

            <div class="summary-row">
                <span class="summary-label">Selected seats</span>
                <span class="summary-value" id="count">0</span>
            </div>

            <div class="summary-divider"></div>

            <div class="total-row">
                <span>Total amount</span>
                <strong>₹<span id="total">0.00</span></strong>
            </div>

            <p class="helper" id="helper">
                Select at least one seat to continue with your booking.
            </p>

            <?php if ($seatCount > 0): ?>
                <button type="submit"
                        class="primary-button"
                        id="submitBtn"
                        form="bookingForm"
                        disabled>
                    Confirm Booking →
                </button>
            <?php endif; ?>

            <a class="back-link" href="index.php">← Back to Home</a>
        </aside>

    </div>

</main>

<footer class="footer">
    © <?php echo date('Y'); ?> CineBook. Your next movie experience starts here.
</footer>

<script>
const checkboxes = document.querySelectorAll(
    'input[name="seat_ids[]"]'
);

const countElement = document.getElementById('count');
const totalElement = document.getElementById('total');
const submitButton = document.getElementById('submitBtn');
const helper = document.getElementById('helper');
const bookingForm = document.getElementById('bookingForm');

function updateSummary() {
    let selectedCount = 0;
    let amount = 0;
    const selectedSeats = [];

    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            selectedCount++;
            amount += Number(checkbox.dataset.price);

            selectedSeats.push(
                checkbox.closest('label').textContent.trim()
            );
        }
    });

    countElement.textContent = selectedCount;
    totalElement.textContent = amount.toFixed(2);

    if (submitButton) {
        submitButton.disabled = selectedCount === 0;
    }

    if (helper) {
        if (selectedCount === 0) {
            helper.textContent =
                'Select at least one seat to continue with your booking.';
        } else {
            helper.textContent =
                selectedCount + ' seat(s) selected: ' +
                selectedSeats.join(', ');
        }
    }
}

checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener('change', updateSummary);
});

if (bookingForm) {
    bookingForm.addEventListener('submit', function (event) {
        const selectedCount = document.querySelectorAll(
            'input[name="seat_ids[]"]:checked'
        ).length;

        if (selectedCount === 0) {
            event.preventDefault();
            alert('Please select at least one seat.');
        }
    });
}

updateSummary();
</script>

</body>
</html>
