<?php
include 'db.php';

?>
</div><!-- /container -->

<?php if (!isset($suppressFooter) || !$suppressFooter): ?>
<footer class="bg-dark text-light mt-5">
  <div class="container py-5">
    <div class="row">
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="d-flex align-items-center mb-3">
          <div class="logo-icon me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">A</div>
          <h5 class="text-uppercase mb-0 fw-bold">Alaa Fashion</h5>
        </div>
        <p class="text-muted mb-4">Your premier destination for fashion-forward clothing. Quality, style, and comfort in every piece we create.</p>
        <div class="social-links d-flex gap-3">
          <a href="#" class="social-link">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="social-link">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="social-link">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="#" class="social-link">
            <i class="fab fa-youtube"></i>
          </a>
        </div>
      </div>
      
      <div class="col-lg-2 col-md-6 mb-4">
        <h6 class="text-uppercase mb-3 fw-bold footer-title">Quick Links</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Shop</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="login.php">Account</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6 mb-4">
        <h6 class="text-uppercase mb-3 fw-bold footer-title">Categories</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="products.php?gender=men">Men's Fashion</a></li>
          <li><a href="products.php?gender=women">Women's Fashion</a></li>
          <li><a href="products.php?gender=kids">Kids Fashion</a></li>
          <li><a href="products.php?class=best_seller">Best Sellers</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6 mb-4">
        <h6 class="text-uppercase mb-3 fw-bold footer-title">Newsletter</h6>
        <p class="text-muted mb-3">Subscribe to get updates on new arrivals and exclusive offers.</p>
        <form class="newsletter-form mb-3">
          <div class="input-group">
            <input type="email" class="form-control newsletter-input" placeholder="Enter your email" required>
            <button class="btn btn-primary newsletter-btn" type="submit">
              <i class="fas fa-paper-plane"></i>
            </button>
          </div>
        </form>
        <div class="contact-info">
          <div class="d-flex align-items-center mb-2">
            <i class="fas fa-phone me-2 text-primary"></i>
            <span class="text-muted">+972599999999</span>
          </div>
          <div class="d-flex align-items-center">
            <i class="fas fa-envelope me-2 text-primary"></i>
            <span class="text-muted">info@Alaafashion.com</span>
          </div>
        </div>
      </div>
    </div>
    
    <hr class="footer-divider my-4">
    
    <div class="row align-items-center">
      <div class="col-md-6">
        <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Alaa Fashion Store. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-md-end">
        <div class="payment-methods d-flex justify-content-md-end gap-2">
          <div class="payment-card">
            <i class="fab fa-cc-visa"></i>
          </div>
          <div class="payment-card">
            <i class="fab fa-cc-mastercard"></i>
          </div>
          <div class="payment-card">
            <i class="fab fa-cc-paypal"></i>
          </div>
          <div class="payment-card">
            <i class="fab fa-cc-stripe"></i>
          </div>
        </div>
        <small class="text-muted d-block mt-2">Secure payments powered by SSL</small>
      </div>
    </div>
  </div>
</footer>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Update cart icon count via AJAX
document.addEventListener('DOMContentLoaded', () => {
  // Initialize Bootstrap dropdowns manually
  const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
  const dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));
  
  // Cart count functionality
  function updateCartIcon() {
    fetch('/ecommerce_store/cart_count.php')
      .then(res => res.text())
      .then(count => {
        const badge = document.getElementById('cart-count');
        if (badge) {
          badge.textContent = count;
          if (count > 0) {
            badge.style.display = 'flex';
          } else {
            badge.style.display = 'none';
          }
        }
      })
      .catch(err => console.error("Error fetching cart count:", err));
  }
  
  updateCartIcon();
  
  // Newsletter subscription
  const newsletterForm = document.querySelector('.newsletter-form');
  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = newsletterForm.querySelector('input[type="email"]').value;
      const button = newsletterForm.querySelector('.newsletter-btn');
      const originalContent = button.innerHTML;
      
      // Show loading state
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
      button.disabled = true;
      
      // Simulate API call
      setTimeout(() => {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast-notification success';
        toast.innerHTML = `
          <i class="fas fa-check-circle me-2"></i>
          Thank you for subscribing to our newsletter!
        `;
        document.body.appendChild(toast);
        
        // Reset form
        newsletterForm.reset();
        button.innerHTML = originalContent;
        button.disabled = false;
        
        // Remove toast after 3 seconds
        setTimeout(() => {
          toast.remove();
        }, 3000);
      }, 1000);
    });
  }
  
  // Smooth scroll for footer links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });
  
  // Debug dropdown
  const userDropdown = document.getElementById('userDropdown');
  if (userDropdown) {
    console.log('User dropdown found');
    userDropdown.addEventListener('click', function(e) {
      console.log('Dropdown clicked');
      e.preventDefault();
      const dropdown = bootstrap.Dropdown.getInstance(this) || new bootstrap.Dropdown(this);
      dropdown.toggle();
    });
  }
});
</script>

<style>
/* Footer Styles */
footer {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  position: relative;
  overflow: hidden;
}

footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
}

.logo-icon {
  background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.footer-title {
  color: #ffffff !important;
  font-weight: 600 !important;
  position: relative;
  padding-bottom: 0.5rem;
  font-size: 1rem;
}

.footer-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 30px;
  height: 2px;
  background: var(--secondary-color);
  border-radius: 1px;
}

.footer-links li {
  margin-bottom: 0.5rem;
}

.footer-links a {
  color: #ecf0f1 !important;
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
  padding-left: 0;
  font-weight: 400;
  font-size: 0.95rem;
}

.footer-links a:hover {
  color: var(--secondary-color) !important;
  padding-left: 10px;
}

.footer-links a::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 1px;
  background: var(--secondary-color);
  transition: width 0.3s ease;
}

.footer-links a:hover::before {
  width: 6px;
}

/* تحسين وضوح النصوص */
footer .text-muted {
  color: #d5dbdb !important;
  font-weight: 400;
}

footer p {
  color: #ecf0f1 !important;
  line-height: 1.6;
}

footer h5 {
  color: #ffffff !important;
  font-weight: 700 !important;
}

.social-links {
  gap: 1rem;
}

.social-link {
  width: 40px;
  height: 40px;
  background: rgba(255,255,255,0.15);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ecf0f1 !important;
  text-decoration: none;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.social-link:hover {
  background: var(--secondary-color);
  color: white !important;
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
}

.newsletter-input {
  background: rgba(255,255,255,0.15) !important;
  border: 1px solid rgba(255,255,255,0.3) !important;
  color: white !important;
  backdrop-filter: blur(10px);
  font-weight: 400;
}

.newsletter-input::placeholder {
  color: rgba(255,255,255,0.8) !important;
  font-weight: 400;
}

.newsletter-input:focus {
  background: rgba(255,255,255,0.2) !important;
  border-color: var(--secondary-color) !important;
  color: white !important;
  box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.newsletter-btn {
  background: var(--secondary-color);
  border: none;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.newsletter-btn:hover {
  background: #2980b9;
  transform: translateY(-1px);
}

.contact-info {
  font-size: 0.95rem;
}

.contact-info span {
  color: #ecf0f1 !important;
  font-weight: 400;
}

.contact-info i {
  color: var(--secondary-color) !important;
}

.payment-methods {
  align-items: center;
}

.payment-card {
  width: 45px;
  height: 30px;
  background: rgba(255,255,255,0.15);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ecf0f1 !important;
  font-size: 1.2rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.payment-card:hover {
  background: rgba(255,255,255,0.25);
  transform: translateY(-2px);
}

.footer-divider {
  border-color: rgba(255,255,255,0.2) !important;
  margin: 2rem 0;
}

/* تحسين النص في الأسفل */
footer .row:last-child p,
footer .row:last-child small {
  color: #d5dbdb !important;
  font-weight: 400;
}

/* Toast Notification */
.toast-notification {
  position: fixed;
  top: 20px;
  right: 20px;
  background: var(--success-color);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
  z-index: 9999;
  animation: slideInRight 0.3s ease;
  font-weight: 500;
}

.toast-notification.success {
  background: linear-gradient(135deg, #27ae60, #2ecc71);
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .payment-methods {
    justify-content: center !important;
    margin-top: 1rem;
  }
  
  .social-links {
    justify-content: center;
  }
  
  footer .col-md-6 {
    text-align: center !important;
  }
  
  .footer-title {
    font-size: 1.1rem;
  }
  
  .footer-links a {
    font-size: 1rem;
  }
}
</style>

</body>
</html>
