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
<title>सर्वेश्वरी मेडिकल कैंप्स</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

</head>

<body class="public-page">

<!-- ===== PUBLIC HEADER ===== -->

<!-- ===== PUBLIC HEADER ===== -->

<?php include 'includes/navbar.php'; ?>
<div class="container text-end mt-3">
<a href="index.php" class="btn btn-outline-dark btn-sm px-3">View in English</a>
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
आध्यात्मिक मूल्य • उत्कृष्ट चिकित्सीय सेवा
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

<strong>अगला शिविर:</strong>
<?= h($next_camp['camp_name']); ?> —
<?= h($next_camp['district']); ?>, <?= h($next_camp['state']); ?> 
 on <?= h($next_camp['camp_date']); ?>

<a href="pre-register.php" class="btn btn-sm btn-dark ms-2">
अभी पंजीकरण करें
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
श्री सर्वेश्वरी समूह के बारे में
</h2>

<h4 class="text-spiritual mb-3">
मानव सेवा ही ईश्वर सेवा: अघोरेश्वर भगवान राम की विरासत</h4>

<p>
जब दुनिया जाति और भेदभाव में बंटी दिखाई देती है, तब श्री सर्वेश्वरी समूह और अवधूत भगवान राम कुष्ठ सेवा आश्रम निस्वार्थ सेवा के प्रकाशस्तंभ बनकर खड़े हैं। परमपूज्य अघोरेश्वर भगवान राम जी द्वारा स्थापित इन संस्थाओं ने अघोर परंपरा को समाज सुधार और जनसेवा के केंद्र में स्थापित किया है।</p>

<h4 class="text-spiritual mb-3">
श्री सर्वेश्वरी समूह: सामाजिक परिवर्तन का अभियान</h4>


<p>
21 सितंबर 1961 को स्थापित श्री सर्वेश्वरी समूह केवल एक संस्था नहीं, बल्कि सामाजिक और आध्यात्मिक जागरण का आंदोलन है। इसका मूल संदेश सरल है: उपेक्षित की सेवा ही ईश्वर की सेवा है।

समूह 19 सूत्रीय कार्यक्रम के माध्यम से समाज की पीड़ा के मूल कारणों को संबोधित करता है। इसके प्रमुख कार्यक्षेत्र हैं:

सामाजिक बुराइयों का उन्मूलन: दहेज, छुआछूत और नशा जैसी कुरीतियों के खिलाफ सतत अभियान।

वंचितों का सशक्तिकरण: महिलाओं के सम्मान और जरूरतमंदों की शिक्षा को बढ़ावा देना।

आत्मनिर्भरता: प्रत्येक सदस्य को श्रम और सेवा के माध्यम से आत्मनिर्भर व संस्कारित जीवन के लिए प्रेरित किया जाता है।
</p>

<h4 class="text-spiritual mb-3">
बाबा भगवान राम कुष्ठ सेवा आश्रम: करुणा का जीवंत उदाहरण</h4>

<p>
बाबा भगवान राम ट्रस्ट की सेवा विभिन्न स्वास्थ्य चुनौतियों तक फैली हुई है। संस्था विशेष रूप से अपने निःशुल्क मिर्गी चिकित्सा शिविरों के लिए प्रसिद्ध है, जिनमें भारत और नेपाल तक से हजारों मरीज आते हैं।</p>
<p>
निःशुल्क परामर्श और विशेष दवाओं के माध्यम से ट्रस्ट यह सुनिश्चित करता है कि उत्कृष्ट न्यूरोलॉजिकल उपचार जरूरतमंद लोगों तक पहुंचे। साथ ही, अघोरेश्वर भगवान राम योग एवं प्राकृतिक चिकित्सा केंद्र शरीर, मन और आत्मा के संतुलन हेतु समग्र स्वास्थ्य कार्यक्रम संचालित करता है।</p>


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
मानव सेवा ही ईश्वर सेवा: अघोरेश्वर भगवान राम की विरासत</h3>

<p>
जब दुनिया जाति और भेदभाव में बंटी दिखाई देती है, तब श्री सर्वेश्वरी समूह और अवधूत भगवान राम कुष्ठ सेवा आश्रम निस्वार्थ सेवा के प्रकाशस्तंभ बनकर खड़े हैं। परमपूज्य अघोरेश्वर भगवान राम जी द्वारा स्थापित इन संस्थाओं ने अघोर परंपरा को समाज सुधार और जनसेवा के केंद्र में स्थापित किया है।</p>

<h3 class="text-spiritual mb-3">
श्री सर्वेश्वरी समूह: सामाजिक परिवर्तन का अभियान</h3>


<p>
21 सितंबर 1961 को स्थापित श्री सर्वेश्वरी समूह केवल एक संस्था नहीं, बल्कि सामाजिक और आध्यात्मिक जागरण का आंदोलन है। इसका मूल संदेश सरल है: उपेक्षित की सेवा ही ईश्वर की सेवा है।

समूह 19 सूत्रीय कार्यक्रम के माध्यम से समाज की पीड़ा के मूल कारणों को संबोधित करता है। इसके प्रमुख कार्यक्षेत्र हैं:

सामाजिक बुराइयों का उन्मूलन: दहेज, छुआछूत और नशा जैसी कुरीतियों के खिलाफ सतत अभियान।

वंचितों का सशक्तिकरण: महिलाओं के सम्मान और जरूरतमंदों की शिक्षा को बढ़ावा देना।

आत्मनिर्भरता: प्रत्येक सदस्य को श्रम और सेवा के माध्यम से आत्मनिर्भर व संस्कारित जीवन के लिए प्रेरित किया जाता है।
</p>

<h3 class="text-spiritual mb-3">
बाबा भगवान राम कुष्ठ सेवा आश्रम: करुणा का जीवंत उदाहरण</h3>

<p>
बाबा भगवान राम ट्रस्ट की सेवा विभिन्न स्वास्थ्य चुनौतियों तक फैली हुई है। संस्था विशेष रूप से अपने निःशुल्क मिर्गी चिकित्सा शिविरों के लिए प्रसिद्ध है, जिनमें भारत और नेपाल तक से हजारों मरीज आते हैं।</p>
<p>
निःशुल्क परामर्श और विशेष दवाओं के माध्यम से ट्रस्ट यह सुनिश्चित करता है कि उत्कृष्ट न्यूरोलॉजिकल उपचार जरूरतमंद लोगों तक पहुंचे। साथ ही, अघोरेश्वर भगवान राम योग एवं प्राकृतिक चिकित्सा केंद्र शरीर, मन और आत्मा के संतुलन हेतु समग्र स्वास्थ्य कार्यक्रम संचालित करता है।</p>


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

<h5>आगामी शिविर</h5>

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

<h5>हमारा प्रभाव</h5>

<p>कुल शिविर: <?= $total_camps ?></p>
<p>कुल मरीज: <?= $total_patients ?></p>
<p>कवर किए गए जिले: <?= $districts ?></p>

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
<p>उपचारित मिर्गी मरीज</p>
</div>

</div>
</div>

</section>-->

<!-- ===== IMPACT STATS ===== -->

<section class="py-5 bg-white orangeborder">

<div class="container">

<h3 class="text-center text-spiritual mb-5">
हमारा प्रभाव
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
गिनीज वर्ल्ड रिकॉर्ड सम्मान
</h3>

<div class="row align-items-center">

<!-- LEFT CONTENT -->
<div class="col-md-6 mb-4">

<p class="lead">
श्री सर्वेश्वरी समूह को विशाल चिकित्सा सेवा अभियानों के सफल आयोजन के लिए विशेष सम्मान प्राप्त हुआ है।
</p>

<p>
समर्पित सेवा, सुव्यवस्थित चिकित्सा शिविरों और सतत मानवीय प्रयासों के माध्यम से संस्था ने ग्रामीण भारत में लाखों लोगों तक पहुंच बनाई है और सामुदायिक स्वास्थ्य सेवा में एक नया मानदंड स्थापित किया है।
</p>

<p>
यह सम्मान स्वयंसेवकों, चिकित्सकों और आध्यात्मिक नेतृत्व की सीमाओं से परे समर्पित सेवा भावना का प्रमाण है।
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
लिम्का वर्ल्ड रिकॉर्ड सम्मान
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
श्री सर्वेश्वरी समूह को विशाल चिकित्सा सेवा अभियानों के सफल आयोजन के लिए विशेष सम्मान प्राप्त हुआ है।
</p>

<p>
समर्पित सेवा, सुव्यवस्थित चिकित्सा शिविरों और सतत मानवीय प्रयासों के माध्यम से संस्था ने ग्रामीण भारत में लाखों लोगों तक पहुंच बनाई है और सामुदायिक स्वास्थ्य सेवा में एक नया मानदंड स्थापित किया है।
</p>

<p>
यह सम्मान स्वयंसेवकों, चिकित्सकों और आध्यात्मिक नेतृत्व की सीमाओं से परे समर्पित सेवा भावना का प्रमाण है।
</p>

</div>

</div>

</div>

</section>

<!-- ===== UPCOMING CAMPS ===== -->

<section class="bg-light py-5 ">

<div class="container">

<h3 class="text-center mb-4">आगामी शिविर</h3>

<div class="row">

<?php
if(mysqli_num_rows($upcoming) > 0){

while($camp = mysqli_fetch_assoc($upcoming)){
?>

<div class="col-md-4 mb-4">

<div class="card shadow">

<div class="card-body">

<h5><?= h($camp['camp_name']) ?></h5>
<p><strong>तारीख:</strong> <?= h($camp['camp_date']) ?></p>
<p><strong>स्थान:</strong> <?= h($camp['district']) ?>, <?= h($camp['state']) ?></p>

<?php if(!empty($camp['location_link'])){ ?>
<a href="<?= h($camp['location_link']) ?>" 
   target="_blank" 
   class="btn btn-sm btn-primary">
लोकेशन देखें
</a>
<?php } ?>

</div>

</div>

</div>

<?php
}

}else{
echo "<p class='text-center'>कोई आगामी शिविर निर्धारित नहीं है।</p>";
}
?>

</div>

</div>

<!-- ===== OUR CAMPS SECTION ===== -->

<section class="py-5 bg-light orangeborder">

<div class="container">

<h3 class="text-center text-spiritual mb-4">
हमारे शिविर
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

<h4>मुख्य कार्यालय</h4>

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

<h4 >संबद्ध ट्रस्ट</h4>

<ul class="footer-list">
<li>Shree Sarweshwari Samooh</li>
<li>Baba Bhagwan Ram Trust</li>
<li>Baba Bhagwan Ram Kusht Seva Ashram</li>
<li>Aghor Parishad</li>
</ul>

</div>

<!-- Column 3 -->
<div class="col-md-4 mb-4">

<h4>चिकित्सा सेवा अभियान</h4>

<p>
मिर्गी जागरूकता एवं उपचार शिविर<br>
ग्रामीण स्वास्थ्य पहल<br>
सामुदायिक चिकित्सा सेवा
</p>

</div>

</div>

<hr style="border-color: rgba(255,255,255,0.3);">

<div class="text-center">
© <?= date('Y'); ?> Shree Sarweshwari Samooh. सभी अधिकार सुरक्षित।
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




