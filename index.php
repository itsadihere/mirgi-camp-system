<?php
include 'includes/config.php';
include 'includes/db_connect.php';

/* ===== LIVE STATS ===== */

$total_camps = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as count FROM camps
"))['count'];

$total_patients = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as count FROM registrations
"))['count'];

$total_epilepsy = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as count
FROM registrations r
JOIN camps c ON r.camp_id = c.camp_id
JOIN diseases d ON c.disease_id = d.disease_id
WHERE d.disease_name='Epilepsy'
"))['count'];

$districts = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(DISTINCT district) as count FROM camps
"))['count'];

/* ===== UPCOMING CAMPS ===== */

$upcoming = mysqli_query($conn,"
SELECT * FROM camps
WHERE status='upcoming'
ORDER BY camp_date ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Sarveshwari Medical Camps</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

</head>

<body class="public-page">

<!-- ===== PUBLIC HEADER ===== -->

<!-- ===== PUBLIC HEADER ===== -->

<?php include 'includes/navbar.php'; ?>
<div class="container text-end mt-3">
<a href="index_hi.php" class="btn btn-outline-dark btn-sm px-3">हिंदी में देखें</a>
</div>


<!-- ===== HERO SECTION ===== -->

<section class="container mt-4 mb-5">

<div id="mainCarousel" class="carousel slide carousel-fade shadow rounded" data-bs-ride="carousel">

<div class="carousel-inner">

<!-- Slide 1 -->
<div class="carousel-item active">

<img src="assets/images/slide1.jpg" class="d-block w-100 carousel-img">

<div class="carousel-caption d-flex flex-column h-50">

<p class="lead fw-bold">
अघोरान्ना परो मन्त्रो नास्ति तत्वं गुरो परम्
</p>


</div>

</div>

<!-- Slide 2 -->
<div class="carousel-item">

<img src="assets/images/slide2.jpg" class="d-block w-100 carousel-img">

<div class="carousel-caption d-flex flex-column justify-content-bottom h-50">

<p class="lead fw-bold">
अघोरान्ना परो मन्त्रो नास्ति तत्वं गुरो परम्
</p>

</div>

</div>

<!-- Slide 3 -->
<div class="carousel-item">

<img src="assets/images/slide3.jpg" class="d-block w-100 carousel-img">

<div class="carousel-caption d-flex flex-column justify-content-center h-50">

<!-- <h1 class="display-4 fw-bold">
Healthcare With Compassion
</h1> -->

<p class="lead fw-bold">
Spiritual Values • Medical Excellence
</p>

</div>

</div>

</div>

</div>

</section>
<div class="container text-center py-1">



</div>
<?php
$next_camp = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT camp_name, camp_date, district, state
FROM camps
WHERE status='upcoming'
ORDER BY camp_date ASC
LIMIT 1
"));
?>

<?php if($next_camp){ ?>

<div class="alert alert-warning text-center mb-0 rounded-0">

<strong>Next Camp:</strong>
<?= h($next_camp['camp_name']); ?> —
<?= h($next_camp['district']); ?>, <?= h($next_camp['state']); ?> 
 on <?= h($next_camp['camp_date']); ?>

<a href="pre-register.php" class="btn btn-sm btn-dark ms-2">
Register Now
</a>

</div>

<?php } ?>
<div class="mb-5"></div>


<!-- ===== ABOUT SECTION ===== -->

<section class="py-5 bg-white orangeborder">

<div class="container">

<div class="row align-items-center">

<!-- LEFT SIDE: IMAGES -->
<div class="col-md-3 text-center mb-3">

<div class="baba-frame-container">

<div class="baba-frame">
<img src="assets/images/baba2.jpg" alt="Baba 1">
</div>
<div class="mb-5"><br></div>
<div class="baba-frame">
<img src="assets/images/baba1.jpg" alt="Baba 2">
</div>

</div>

</div>

<!-- RIGHT SIDE: TEXT -->
<div class="col-md-9">

<h2 class="text-spiritual mb-3">
About Shree Sarweshwari Samooh
</h2>

<h4 class="text-spiritual mb-3">
Serving Humanity as Divinity: The Legacy of Aghoreshwar Bhagwan Ram</h4>

<p>
In a world often divided by creed and caste, Shree Sarweshwari Samooh and the Avadhoot Bhagwan Ram Kusht Seva Ashram stand as beacons of selfless service. Founded by the legendary saint Parampujya Aghoreshwar Bhagwan Ram Ji, these institutions have redefined the ancient Aghor tradition, taking it from the secluded cremation grounds to the heart of social reform.</p>

<h4 class="text-spiritual mb-3">
Shree Sarweshwari Samooh: A Movement for Social Change</h4>


<p>
Established on September 21, 1961, Shree Sarweshwari Samooh is not just an organization; it is a socio-spiritual movement. Its philosophy is simple yet profound: to serve the neglected is to serve the Divine.

The Samooh operates on a dedicated 19-Point Programme that tackles the root causes of social suffering. Its core initiatives include:

Eradicating Social Evils: Active campaigning against the dowry system, untouchability, and substance abuse.

Empowerment of the Marginalized: Promoting the dignity of women and providing education to the underprivileged.

Self-Reliance: Every member is encouraged to engage in physical labor, fostering a community that is spiritually grounded and economically independent.
</p>

<h4 class="text-spiritual mb-3">
Baba Bhagwan Ram Kusht Seva Ashram: A Miracle of Compassion</h4>

<p>
The mission of the Baba Bhagwan Ram Trust extends to various healthcare crises. The organization is widely recognized for its Free Epilepsy Camps, which draw thousands of patients from across India and neighboring countries like Nepal.</p>
<p>
By providing free consultations and specialized medicines, the Trust ensures that high-quality neurological care is accessible to those who need it most. Additionally, the Aghoreshwar Bhagwan Ram Yoga and Naturopathy Research Center offers holistic wellness programs that balance the body, mind, and spirit.</p>


</div>

</div>

</div>

</section><br>

<!-- </section>

<section class="py-5 bg-white">

<div class="container">

<div class="row">

 ----LEFT CONTENT-----
 
<div class="col-md-8">



<h3 class="text-spiritual mb-3">
Serving Humanity as Divinity: The Legacy of Aghoreshwar Bhagwan Ram</h3>

<p>
In a world often divided by creed and caste, Shree Sarweshwari Samooh and the Avadhoot Bhagwan Ram Kusht Seva Ashram stand as beacons of selfless service. Founded by the legendary saint Parampujya Aghoreshwar Bhagwan Ram Ji, these institutions have redefined the ancient Aghor tradition, taking it from the secluded cremation grounds to the heart of social reform.</p>

<h3 class="text-spiritual mb-3">
Shree Sarweshwari Samooh: A Movement for Social Change</h3>


<p>
Established on September 21, 1961, Shree Sarweshwari Samooh is not just an organization; it is a socio-spiritual movement. Its philosophy is simple yet profound: to serve the neglected is to serve the Divine.

The Samooh operates on a dedicated 19-Point Programme that tackles the root causes of social suffering. Its core initiatives include:

Eradicating Social Evils: Active campaigning against the dowry system, untouchability, and substance abuse.

Empowerment of the Marginalized: Promoting the dignity of women and providing education to the underprivileged.

Self-Reliance: Every member is encouraged to engage in physical labor, fostering a community that is spiritually grounded and economically independent.
</p>

<h3 class="text-spiritual mb-3">
Baba Bhagwan Ram Kusht Seva Ashram: A Miracle of Compassion</h3>

<p>
The mission of the Baba Bhagwan Ram Trust extends to various healthcare crises. The organization is widely recognized for its Free Epilepsy Camps, which draw thousands of patients from across India and neighboring countries like Nepal.</p>
<p>
By providing free consultations and specialized medicines, the Trust ensures that high-quality neurological care is accessible to those who need it most. Additionally, the Aghoreshwar Bhagwan Ram Yoga and Naturopathy Research Center offers holistic wellness programs that balance the body, mind, and spirit.</p>


<h4 class="mt-4 text-spiritual">
Our Philosophy
</h4>

<ul>
<li>Seva beyond caste, creed and religion</li>
<li>Medical service as spiritual practice</li>
<li>Rural healthcare empowerment</li>
<li>Compassion-driven community outreach</li>
</ul>



</div>


 RIGHT SIDE CARDS 
<div class="col-md-4">

<div class="card shadow mb-3 p-3">

<h5>Upcoming Camps</h5>

<?php
$upcoming_small = mysqli_query($conn,"
SELECT camp_name, camp_date
FROM camps
WHERE status='upcoming'
ORDER BY camp_date ASC
LIMIT 3
");

if(mysqli_num_rows($upcoming_small)>0){
while($u = mysqli_fetch_assoc($upcoming_small)){
echo "<p class='mb-1'>
<strong>{$u['camp_name']}</strong><br>
<small>{$u['camp_date']}</small>
</p>";
}
}else{
echo "<p>No upcoming camps.</p>";
}
?>

</div>

<div class="card shadow p-3">

<h5>Our Impact</h5>

<p>Total Camps: <?= $total_camps ?></p>
<p>Total Patients: <?= $total_patients ?></p>
<p>Districts Covered: <?= $districts ?></p>

</div>

</div>

</div>

</div>

</section> 

<!-- ===== LIVE STATS ===== -->

<!-- <section class="py-5 text-center">

<div class="container">
<div class="row">

<div class="col-md-3">
<h2><?= $total_camps ?></h2>
<p>Total Camps Conducted</p>
</div>

<div class="col-md-3">
<h2><?= $total_patients ?></h2>
<p>Total Patients Treated</p>
</div>

<div class="col-md-3">
<h2><?= $districts ?></h2>
<p>Districts Covered</p>
</div>

<div class="col-md-3">
<h2><?= $total_epilepsy ?></h2>
<p>Epilepsy Patients Treated</p>
</div>

</div>
</div>

</section>-->

<!-- ===== IMPACT STATS ===== -->

<section class="py-5 bg-white orangeborder">

<div class="container">

<h3 class="text-center text-spiritual mb-5">
Our Impact
</h3>

<div class="row text-center">

<div class="col-md-3 col-6">
<div class="impact-box">
<h2 class="counter" data-target="500">0+</h2>
<p>Total Camps Conducted</p>
</div>
</div>

<div class="col-md-3 col-6">
<div class="impact-box">
<h2 class="counter" data-target="400000">0+</h2>
<p>Patients Treated</p>
</div>
</div>

<div class="col-md-3 col-6">
<div class="impact-box">
<h2 class="counter" data-target="100">0+</h2>
<p>Districts Covered</p>
</div>
</div>

<div class="col-md-3 col-6">
<div class="impact-box">
<h2 class="counter" data-target="160000">0+</h2>
<p>Epilepsy Patients Served</p>
</div>
</div>

</div>

</div>

</section>
<br>

<!-- ===== GUINNESS RECORD SECTION ===== -->

<section class="py-5 guinness-section">

<div class="container">

<h3 class="text-center text-spiritual mb-5">
Guinness World Record Recognition
</h3>

<div class="row align-items-center">

<!-- LEFT CONTENT -->
<div class="col-md-6 mb-4">

<p class="lead">
Shree Sarweshwari Samooh has been honored with recognition for its extraordinary contribution in organizing large-scale medical outreach initiatives.
</p>

<p>
Through dedicated service, structured medical camps, and continuous humanitarian efforts, the organization has reached millions across rural India, setting a benchmark in community healthcare excellence.
</p>

<p>
This recognition stands as a testament to the collective commitment of volunteers, doctors, and spiritual leadership devoted to service beyond boundaries.
</p>

</div>

<!-- RIGHT IMAGE -->
<div class="col-md-6 text-center">

<img src="assets/images/guinnessbook.jpg"
     class="img-fluid shadow rounded guinness-img"
     alt="Guinness World Record Certificate">

</div>

</div>

</div>

</section>
<br>
<!-- ===== lIMCA RECORD SECTION ===== -->

<section class="py-5 guinness-section">

<div class="container">

<h3 class="text-center text-spiritual mb-5">
Limca World Record Recognition
</h3>

<div class="row align-items-center">

<!-- LEFT CONTENT -->

<div class="col-md-6 text-center">

<img src="assets/images/limca.jpg"
     class="img-fluid shadow rounded guinness-img"
     alt="Guinness World Record Certificate">

</div>

<!-- RIGHT IMAGE -->

<div class="col-md-6 mb-4">

<p class="lead">
Shree Sarweshwari Samooh has been honored with recognition for its extraordinary contribution in organizing large-scale medical outreach initiatives.
</p>

<p>
Through dedicated service, structured medical camps, and continuous humanitarian efforts, the organization has reached millions across rural India, setting a benchmark in community healthcare excellence.
</p>

<p>
This recognition stands as a testament to the collective commitment of volunteers, doctors, and spiritual leadership devoted to service beyond boundaries.
</p>

</div>

</div>

</div>

</section>

<!-- ===== UPCOMING CAMPS ===== -->

<section class="bg-light py-5 ">

<div class="container">

<h3 class="text-center mb-4">Upcoming Camps</h3>

<div class="row">

<?php
if(mysqli_num_rows($upcoming) > 0){

while($camp = mysqli_fetch_assoc($upcoming)){
?>

<div class="col-md-4 mb-4">

<div class="card shadow">

<div class="card-body">

<h5><?= h($camp['camp_name']) ?></h5>
<p><strong>Date:</strong> <?= h($camp['camp_date']) ?></p>
<p><strong>Location:</strong> <?= h($camp['district']) ?>, <?= h($camp['state']) ?></p>

<?php if(!empty($camp['location_link'])){ ?>
<a href="<?= h($camp['location_link']) ?>" 
   target="_blank" 
   class="btn btn-sm btn-primary">
View Location
</a>
<?php } ?>

</div>

</div>

</div>

<?php
}

}else{
echo "<p class='text-center'>No upcoming camps scheduled.</p>";
}
?>

</div>

</div>

<!-- ===== OUR CAMPS SECTION ===== -->

<section class="py-5 bg-light orangeborder">

<div class="container">

<h3 class="text-center text-spiritual mb-4">
Our Camps
</h3>

<div class="camp-slider">

<div class="camp-track">

<img src="assets/images/camps/1.jpeg">
<img src="assets/images/camps/2.jpeg">
<img src="assets/images/camps/3.jpeg">
<img src="assets/images/camps/4.jpeg">
<img src="assets/images/camps/5.jpeg">
<img src="assets/images/camps/6.jpeg">
<img src="assets/images/camps/7.jpeg">
<img src="assets/images/camps/8.jpeg">
<img src="assets/images/camps/9.jpeg">
<img src="assets/images/camps/10.jpeg">

<!-- Duplicate images for smooth infinite effect -->

<img src="assets/images/camps/1.jpeg">
<img src="assets/images/camps/2.jpeg">
<img src="assets/images/camps/3.jpeg">
<img src="assets/images/camps/4.jpeg">
<img src="assets/images/camps/5.jpeg">

</div>

</div>

</div>

</section><br>


<!-- ===== FOOTER ===== -->

<footer class="main-footer text-white">

<div class="container py-5">

<div class="row">

<!-- Column 1 -->
<div class="col-md-4 mb-4">

<h4>Head Office</h4>

<p>
Shree Sarweshwari Samooh<br>
Padao, Varanasi<br>
Uttar Pradesh, India
</p>

<p>
Phone: +91-XXXXXXXXXX<br>
Email: info@example.com
</p>

</div>

<!-- Column 2 -->
<div class="col-md-4 mb-4">

<h4 >Associated Trusts</h4>

<ul class="footer-list">
<li>Shree Sarweshwari Samooh</li>
<li>Baba Bhagwan Ram Trust</li>
<li>Baba Bhagwan Ram Kusht Seva Ashram</li>
<li>Aghor Parishad</li>
</ul>

</div>

<!-- Column 3 -->
<div class="col-md-4 mb-4">

<h4>Medical Outreach</h4>

<p>
Epilepsy Awareness & Treatment Camps<br>
Rural Healthcare Initiatives<br>
Community Medical Seva
</p>

</div>

</div>

<hr style="border-color: rgba(255,255,255,0.3);">

<div class="text-center">
© <?= date('Y'); ?> Shree Sarweshwari Samooh. All Rights Reserved.
</div>

</div>

</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(){

    const counters = document.querySelectorAll(".counter");

    counters.forEach(counter => {

        const target = +counter.getAttribute("data-target");
        let count = 0;

        const speed = target / 200;

        const updateCount = () => {

            count += speed;

            if(count < target){
                counter.innerText = Math.floor(count).toLocaleString() + "+";
                requestAnimationFrame(updateCount);
            } else {
                counter.innerText = target.toLocaleString() + "+";
            }
        };

        updateCount();
    });

});
</script>
</body>
</html>



