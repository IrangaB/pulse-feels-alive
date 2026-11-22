<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';
$c = content();
$products = visible_items($c['products']);
$fabrics = visible_items($c['fabrics']);
$hero = $c['hero'];
$intro = $c['intro'];
$story = $c['story'];
$contact = $c['contact'];
$sent = isset($_GET['sent']);
$static = is_static(); // true when building the GitHub review preview
render_head('PULSE · FEELS ALIVE | Custom Apparel', 'PULSE · FEELS ALIVE creates fully customised printed apparel and accessories for teams, clubs, businesses and brands.');
render_header('home');
?>
  <main>
    <section class="hero" id="home">
      <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?= h($hero['poster']) ?>">
        <source src="<?= h($hero['video']) ?>" type="video/mp4">
      </video>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <p class="eyebrow"><?= h($hero['eyebrow']) ?></p>
        <h1><?= title_html($hero['title_line1'], $hero['title_line2'], true) ?></h1>
        <p class="hero-intro"><?= h($hero['intro']) ?></p>
        <div class="hero-actions">
          <a class="button button-red" href="#products">Explore Products</a>
          <a class="button button-light" href="#contact">Request a Quote</a>
        </div>
      </div>
      <div class="hero-caption"><span><?= h($hero['caption_left']) ?></span><span><?= h($hero['caption_right']) ?></span></div>
    </section>

    <section class="intro section">
      <p class="eyebrow"><?= h($intro['eyebrow']) ?></p>
      <h2><?= title_html($intro['title'], $intro['title_accent']) ?></h2>
      <p><?= h($intro['text']) ?></p>
    </section>

    <section class="section story" id="story">
      <div class="section-title">
        <p class="eyebrow"><?= h($story['eyebrow']) ?></p>
        <h2><?= title_html($story['title'], $story['title_accent']) ?></h2>
      </div>
      <div class="story-layout">
        <div class="story-visual">
          <img src="<?= h($story['image']) ?>" alt="PULSE apparel production and design" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
          <div class="image-placeholder" hidden>ADD A PULSE WORKSHOP, TEAM OR BRAND PHOTO</div>
        </div>
        <div class="story-text">
          <h3><?= title_html($story['heading_line1'], $story['heading_line2'], true) ?></h3>
          <?= paragraphs($story['text']) ?>
          <?php if (trim($story['highlight']) !== ''): ?><p class="highlight-line"><?= h($story['highlight']) ?></p><?php endif; ?>
        </div>
      </div>
    </section>

    <section class="facts-image-strip" aria-label="PULSE order and delivery features">
      <img
        src="<?= h($c['features']['image']) ?>"
        alt="<?= h($c['features']['alt']) ?>"
        loading="lazy"
      >
    </section>

    <section class="section" id="fabrics">
      <div class="section-title">
        <p class="eyebrow"><?= h($c['fabrics_section']['eyebrow']) ?></p>
        <h2><?= title_html($c['fabrics_section']['title'], $c['fabrics_section']['title_accent']) ?></h2>
        <p><?= h($c['fabrics_section']['text']) ?></p>
      </div>
      <div class="fabric-grid">
<?php foreach ($fabrics as $fabric): ?>
        <article class="fabric-card">
          <img src="<?= h($fabric['image']) ?>" alt="<?= h($fabric['name']) ?> fabric" loading="lazy">
          <div class="card-body"><h3><?= h($fabric['name']) ?></h3><?php if (!empty($fabric['description'])): ?><p><?= h($fabric['description']) ?></p><?php endif; ?></div>
        </article>
<?php endforeach; ?>
      </div>
    </section>

    <section class="section products-section" id="products">
      <div class="section-title">
        <p class="eyebrow"><?= h($c['products_section']['eyebrow']) ?></p>
        <h2><?= title_html($c['products_section']['title'], $c['products_section']['title_accent']) ?></h2>
        <p><?= h($c['products_section']['text']) ?></p>
      </div>
      <div class="product-grid">
<?php foreach ($products as $product): ?>
        <article class="product-card">
          <img src="<?= h($product['image']) ?>" alt="PULSE <?= h($product['name']) ?>" loading="lazy">
        </article>
<?php endforeach; ?>
      </div>
    </section>

    <section class="contact-section" id="contact">
      <div class="contact-copy">
        <p class="eyebrow"><?= h($contact['eyebrow']) ?></p>
        <h2><?= title_html($contact['title_line1'], $contact['title_line2'], true) ?></h2>
        <p><?= h($contact['text']) ?></p>
        <ul><?php foreach (lines($contact['bullets']) as $bullet): ?><li><?= h($bullet) ?></li><?php endforeach; ?></ul>
      </div>
      <form class="contact-form" id="contactForm" action="contact.php" method="post"<?= $static ? ' data-preview="1"' : '' ?>>
        <div id="form-fields"<?= $sent ? ' hidden' : '' ?>>
          <div class="form-field"><label for="f-name">Name *</label><input id="f-name" name="name" type="text" autocomplete="name" maxlength="120" required></div>
          <div class="form-field"><label for="f-email">Email *</label><input id="f-email" name="email" type="email" autocomplete="email" maxlength="200" required></div>
          <div class="form-field"><label for="f-phone">Phone</label><input id="f-phone" name="phone" type="tel" autocomplete="tel" maxlength="40"></div>
          <div class="form-field"><label for="f-organisation">Team, club or company</label><input id="f-organisation" name="organisation" type="text" autocomplete="organization" maxlength="160"></div>
          <div class="form-field"><label for="f-product">Product</label>
            <select id="f-product" name="product">
              <option value="">Please select</option>
<?php foreach ($products as $product): ?>
              <option><?= h($product['name']) ?></option>
<?php endforeach; ?>
              <option>Other / not sure yet</option>
            </select>
          </div>
          <div class="form-field"><label for="f-quantity">Quantity</label><input id="f-quantity" name="quantity" type="number" min="1" step="1" inputmode="numeric" placeholder="Minimum 10 pieces"></div>
          <div class="form-field"><label for="f-message">Message *</label><textarea id="f-message" name="message" rows="5" maxlength="5000" required placeholder="Tell us about your design, colours and timeline."></textarea></div>
          <div class="hidden-field" aria-hidden="true"><label for="f-website">Leave this field empty</label><input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
          <input type="hidden" name="started" value="<?= time() ?>">
        </div>
        <button class="button button-red" id="form-submit" type="submit"<?= $sent ? ' hidden' : '' ?>>Send Enquiry <span>↗</span></button>
        <p class="form-note" id="form-status" role="status"><?= $sent
            ? 'Thank you for contacting PULSE. We have received your enquiry and will get back to you.'
            : ($static ? 'Review preview: the enquiry form is switched off here and will work on the final website.' : 'Submitting this form allows PULSE to contact you about your enquiry.') ?></p>
      </form>
    </section>
  </main>
<?php render_footer($c, 'home'); ?>
