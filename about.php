<?php include 'includes/header.php'; ?>

<div class="about-container">
    <h2>About Us</h2>
    <p>Welcome to <strong>Herbs Haven</strong>, your one-stop destination for all herbal products.</p>

    <div class="about-content">
        <div class="about-text">
            <p>We offer a wide range of herbs at affordable prices. Our products are made from the finest herbs, carefully selected to ensure the best quality. Whether you're looking for traditional remedies or natural wellness solutions, we have something for everyone.</p>
            <p>At HerbBooking, we are committed to providing the best service to our customers. Our team of experts is passionate about herbs and dedicated to helping you find the perfect herbs for your needs.</p>
        </div>
        
        <div class="about-vision">
            <h3>Our Vision</h3>
            <p>We aim to educate and empower people by promoting the benefits of herbal products and providing easy access to top-quality herbs. Our goal is to make herbal health solutions available to everyone, improving lives one herb at a time.</p>
        </div>
    </div>

    <div class="footer-note">
        <p><strong style="color: green;">T-Nets</strong> - All rights reserved</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<style>
    /* About Us Page Styles */
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    background-color: #f9f9f9;
    color: #333;
}

.about-container h2 {
    font-size: 36px;
    color: #2c3e50;
    margin-bottom: 20px;
    text-align: center;
}

.about-container p {
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 20px;
    text-align: justify;
    color: #555;
}

/* About Content Layout */
.about-content {
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    margin-top: 30px;
}

.about-text {
    flex: 1;
    min-width: 300px;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.about-vision {
    flex: 1;
    min-width: 300px;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.about-vision h3 {
    font-size: 24px;
    color: #27ae60;
    margin-bottom: 15px;
}

.footer-note {
    text-align: center;
    margin-top: 50px;
    font-size: 16px;
    color: #777;
}

.footer-note strong {
    color: #27ae60;
    font-weight: bold;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .about-container {
        padding: 20px;
    }

    .about-content {
        flex-direction: column;
        align-items: center;
    }

    .about-text, .about-vision {
        min-width: 100%;
    }

    .about-container h2 {
        font-size: 28px;
    }

    .about-container p {
        font-size: 16px;
    }
}
</style>