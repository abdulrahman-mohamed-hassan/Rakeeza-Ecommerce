<?php ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
.site-footer{
    background:#2c3e50;
    color:#ecf0f1;
    font-family:Arial, sans-serif;
    width:100vw;
    margin-left:calc(-50vw + 50%);
    margin-right:calc(-50vw + 50%);
    padding:50px 0 30px;
}

.footer-container{
    max-width:1200px;
    margin:0 auto;
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:40px;
    padding:0 24px;
}

.footer-section h3{
    color:#cfa967;
    font-size:16px;
    margin-bottom:20px;
    text-transform:uppercase;
}

.social-icons{
    display:flex;
    gap:20px;
}

.social-icons a{
    font-size:28px;
    color:#ecf0f1;
}

.address-info{
    font-size:14px;
    line-height:1.8;
    color:#bdc3c7;
}


.footer-links li{
    margin-bottom:12px;
}

.footer-links a{
    color:#bdc3c7;
    text-decoration:none;
    font-size:14px;
}

.footer-bottom{
    margin-top:40px;
    padding-top:25px;
    border-top:1px solid rgba(207,169,103,.2);
}
.footer-links ul{
    display:flex;
    flex-direction:column;
    align-items:center;
}


.footer-bottom .footer-container{
    display:flex;
    flex-direction:column;
    align-items:center;
    gap:20px;
}

.payment-icons{
    display:flex;
    gap:20px;
}

.payment-icons i{
    font-size:32px;
}

.copyright{
    font-size:13px;
    color:#bdc3c7;
    text-align:center;
}

@media(max-width:992px){
    .footer-container{
        grid-template-columns:1fr 1fr;
    }
}

@media(max-width:768px){
    .footer-container{
        grid-template-columns:1fr;
        text-align:center;
    }
    .social-icons{
        justify-content:center;
    }
}
</style>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-x-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>

        <div class="footer-section">
            <h3>Our Location</h3>
            <div class="address-info">
                <strong>Rakeeza Company</strong><br>
                64 orabi Street maadi<br>
                City, cairo<br>
                egypt<br><br>
                Phone: (20) 112944557<br>
                Email: rakeeza@gmail.com
            </div>
        </div>

        <div class="footer-section footer-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="wishlist.php">Wishlist</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-container">
            <div class="payment-icons">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-paypal"></i>
                <i class="fab fa-cc-amex"></i>
            </div>
            <div class="copyright">
                © 2025 Rakeeza Company. All rights reserved.
            </div>
        </div>
    </div>
</footer>
