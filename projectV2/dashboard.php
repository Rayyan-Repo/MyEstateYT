<?php  
include 'components/connect.php'; //
$user_id = validate_user_cookie($conn);
if(!$user_id){ header('location:login.php'); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Elite Dashboard | MyEstateYT</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="css/style.css">
   <style>
      /* Ultra-Premium Overrides */
      .elite-listings { padding: 4rem 2rem; background: #fdfdfd; }
      .p-card { background: #fff; border-radius: 1.5rem; overflow: hidden; border: 1px solid #eee; transition: .4s cubic-bezier(0.4, 0, 0.2, 1); height: 100%; position: relative; }
      .p-card:hover { transform: translateY(-12px); box-shadow: 0 25px 50px -12px rgba(214, 40, 40, 0.15); border-color: var(--main-color); }
      .p-img-box { position: relative; height: 22rem; overflow: hidden; }
      .p-img-box img { width: 100%; height: 100%; object-fit: cover; transition: .6s; }
      .p-card:hover .p-img-box img { transform: scale(1.08); }
      .p-badge { position: absolute; top: 1.5rem; right: 1.5rem; background: rgba(255,255,255,0.9); backdrop-filter: blur(5px); color: var(--main-color); padding: .6rem 1.2rem; border-radius: 5rem; font-weight: 700; font-size: 1.4rem; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
      .p-content { padding: 2.2rem; }
      .p-price { font-size: 2.2rem; font-weight: 800; color: var(--black); margin-bottom: .8rem; }
      .p-name { font-size: 1.8rem; font-weight: 600; color: #444; margin-bottom: 1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .p-loc { font-size: 1.4rem; color: #777; margin-bottom: 2rem; display: flex; align-items: center; gap: .8rem; }
      .p-btn { display: flex; align-items: center; justify-content: center; gap: 1rem; width: 100%; padding: 1.5rem; background: #1a1a1a; color: #fff; font-weight: 600; font-size: 1.5rem; border-radius: 0; transition: .3s; }
      .p-btn:hover { background: var(--main-color); }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="dashboard">
   <h1 class="heading">System Overview</h1>
   <div class="box-container">
      </div>
</section>

<section class="elite-listings">
   <h1 class="heading">exclusive collections</h1>
   <div class="swiper property-slider">
      <div class="swiper-wrapper">
         <?php
            $select_listings = $conn->prepare("SELECT * FROM `property` ORDER BY date DESC LIMIT 6");
            $select_listings->execute();
            if($select_listings->rowCount() > 0){
               while($fetch_listing = $select_listings->fetch(PDO::FETCH_ASSOC)){
         ?>
         <div class="swiper-slide">
            <div class="p-card">
               <div class="p-img-box">
                  <div class="p-badge">New Arrival</div>
                  <img src="uploaded_files/<?= $fetch_listing['image_01']; ?>" alt="">
               </div>
               <div class="p-content">
                  <div class="p-price">₹<?= $fetch_listing['price']; ?></div>
                  <h3 class="p-name"><?= $fetch_listing['property_name']; ?></h3>
                  <p class="p-loc"><i class="fas fa-map-marker-alt"></i> <?= $fetch_listing['address']; ?></p>
                  <a href="view_property.php?get_id=<?= $fetch_listing['id']; ?>" class="p-btn">
                     View Property Details <i class="fas fa-arrow-right"></i>
                  </a>
               </div>
            </div>
         </div>
         <?php
               }
            } else {
               echo '<p class="empty">Luxury properties arriving soon.</p>';
            }
         ?>
      </div>
      <div class="swiper-pagination" style="bottom: -5px;"></div>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script>
   new Swiper(".property-slider", {
      loop: true,
      spaceBetween: 30,
      autoplay: { delay: 4500, disableOnInteraction: false },
      pagination: { el: ".swiper-pagination", clickable: true },
      breakpoints: {
         0: { slidesPerView: 1 },
         768: { slidesPerView: 2 },
         1100: { slidesPerView: 3 },
      }
   });
</script>
</body>
</html>