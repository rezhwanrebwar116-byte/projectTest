<?php
session_start();
include "config.php";

if (!isset($_GET['id'])) {
    die("No car selected");
}

$id = intval($_GET['id']);

$result = mysqli_query($con, "SELECT * FROM cars WHERE id='$id'");

if (!$result || mysqli_num_rows($result) == 0) {
    die("<div class='text-center text-red-500 mt-10 text-xl'>Car not found</div>");
}

$row = mysqli_fetch_assoc($result);

$isAdmin = (isset($_SESSION['email']) && $_SESSION['email'] === "admin@gmail.com");

// FIX: All POST logic moved BEFORE HTML output so header() redirects work correctly

// ADMIN: UPDATE
if (isset($_POST['update'])) {
    $car_id = intval($_POST['car_id']);
    header("Location: update.php?id=" . $car_id);
    exit();
}

// ADMIN: DELETE
if (isset($_POST['delete'])) {
    $car_id = intval($_POST['car_id']);
    $delete = mysqli_query($con, "DELETE FROM cars WHERE id='$car_id'");
    if ($delete) {
        echo "<script>alert('Car deleted successfully!'); window.location.href='car.php';</script>";
        exit();
    } else {
        $error = "Delete failed: " . mysqli_error($con);
    }
}

// USER: BUY
if (isset($_POST['buy'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    if ($isAdmin) {
        $error = "Admin cannot buy cars.";
    } else {
        $car_id  = intval($_POST['car_id']);
        $user_id = intval($_SESSION['user_id']);

        $insert = mysqli_query($con,
            "INSERT INTO orders (car_id, user_id) VALUES ('$car_id', '$user_id')"
        );

        if ($insert) {
            header("Location: detels.php?id=$id&success=1");
            exit();
        } else {
            $error = "Database Error: " . mysqli_error($con);
        }
    }
}

if (isset($_GET['success'])) {
    $success = "✅ Order placed successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Car Details</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

<!-- ALERTS -->
<?php if (!empty($success)): ?>
<div class="alert alert-success text-center mt-3">
    <?php echo $success; ?>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger text-center mt-3">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<div class="max-w-5xl mx-auto p-4">

<div class="bg-white rounded-2xl shadow-lg grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- IMAGE -->
    <div class="flex items-center justify-center bg-gray-100 p-4">
        <img src="upload/<?php echo htmlspecialchars($row['image']); ?>"
             class="max-h-80 object-contain rounded-lg hover:scale-105 transition"
             onerror="this.src='car.jpg'">
    </div>

    <!-- DETAILS -->
    <div class="p-6 flex flex-col">

        <h2 class="text-2xl font-bold text-gray-800">
            <?php echo htmlspecialchars($row['brand']); ?>
        </h2>

        <h3 class="text-gray-500">
            <?php echo htmlspecialchars($row['name']); ?>
        </h3>

        <p class="mt-3">
            <b>Model:</b> <?php echo htmlspecialchars($row['model']); ?>
        </p>

        <p>
            <b>Price:</b>
            <span class="text-green-600 font-bold">
                $<?php echo number_format($row['price'], 2); ?>
            </span>
        </p>

        <p class="mt-4 text-gray-600">
            <?php echo htmlspecialchars($row['detels']); ?>
        </p>

        <!-- BUTTONS -->
        <div class="mt-auto pt-6">

            <?php if ($isAdmin): ?>

                <!-- ADMIN BUTTONS -->
                <form action="" method="POST">
                    <input type="hidden" name="car_id" value="<?php echo $row['id']; ?>">

                    <div class="flex gap-3 mt-4">

                        <!-- FIX: typo "Uptade" → "Update" -->
                        <button type="submit" name="update"
                            class="flex-1 flex items-center justify-center gap-2 bg-amber-500 text-white px-4 py-2.5 rounded-lg font-semibold shadow-sm hover:bg-amber-600 active:scale-95 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Update
                        </button>

                        <button type="submit" name="delete"
                            onclick="return confirm('Are you sure you want to delete this car?')"
                            class="flex-1 flex items-center justify-center gap-2 bg-white text-red-600 border border-red-200 px-4 py-2.5 rounded-lg font-semibold hover:bg-red-50 hover:border-red-300 active:scale-95 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>

                    </div>
                </form>

            <?php else: ?>

                <!-- USER BUY -->
                <form method="POST">
                    <input type="hidden" name="car_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="buy"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                        🛒 Buy Now
                    </button>
                </form>

            <?php endif; ?>

            <!-- BACK -->
            <a href="car.php"
               class="block text-center mt-3 bg-gray-200 py-3 rounded-lg hover:bg-gray-300 transition">
               ← Back
            </a>

        </div>

        <!-- USER INFO -->
        <?php if (isset($_SESSION['user'])): ?>
        <p class="text-xs text-gray-400 mt-4 text-center">
            Logged in as: <b><?php echo htmlspecialchars($_SESSION['user']); ?></b>
        </p>
        <?php endif; ?>

    </div>

</div>
</div>

</body>
</html>