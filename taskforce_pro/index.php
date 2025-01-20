<?php include 'header.php'; ?>
<main class="welcome-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to <span class="brand-name">Task Force Pro Wallet</span></h1>
            <p class="hero-description">
                Your ultimate tool to track expenses, manage budgets, and gain control over your finances. 
                Make every penny count and take charge of your financial future!
            </p>
            <a href="profile.php" class="cta-button">Your profile</a>
        </div>
        <div class="hero-image">
            <img src="logo.png" alt="Task Force Pro Wallet Dashboard Illustration">
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
<style>
    /* Welcome Section Styles */
.welcome-section {
    padding: 60px 20px;
    background: linear-gradient(135deg, #f8fafc, #e8efff);
    text-align: center;
}

.hero-container {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.hero-content {
    flex: 1 1 50%;
    max-width: 600px;
}

.hero-title {
    font-size: 2.5rem;
    color: #263544;
    font-weight: 700;
    margin-bottom: 20px;
}

.brand-name {
    color: #007bff;
}

.hero-description {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 30px;
    line-height: 1.8;
}

.cta-button {
    display: inline-block;
    padding: 12px 30px;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    background-color: #007bff;
    border-radius: 25px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.cta-button:hover {
    background-color: #0056b3;
}

.hero-image {
    flex: 1 1 50%;
    max-width: 500px;
}

.hero-image img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

</style>