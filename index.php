<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bangladesh Travel Diaries</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<!---username section--->
<div class="username-section" id="username-section">
  <h2 class="username-title">Welcome to ‘Bangladesh Travel Diaries’!</h2>

  <form action="#" method="post" autocomplete="off" class="username-form" onsubmit="return false;">
    <label for="name">Enter your username:</label>
    <input type="text" id="username" placeholder="Type your username here" autocomplete="off"/>
    <button type="button" id="signin-btn">Sign in</button>
  </form>
</div>

<!---article section--->
<div class="articles-container hidden" id="articles-section">
  <h2 class="article-title">BANGLADESH TRAVEL DIARIES</h2>

  <!-- STATIC ARTICLES -->
  <div id="static-articles">
    <div class="first-article">
      <div class="first-article-content">
        <h3>Kayaking in Thanchi</h3>
        <p class="meta">raihan_ahmed | 21/02/2017 – 10:04:33 GMT</p>
        <p class="text">
          On a bright February morning, we set off from Thanchi. It is a common route to Modok M
        </p>
      </div>
      <div class="article-flagging">
        <button class="flag-btn">Flag</button>
        <div class="flag-options" style="display: none;">
          <div class="flag-options-content">
            <label><input type="checkbox"> Abusive</label><br>
            <label><input type="checkbox"> Spam</label><br>
            <label><input type="checkbox"> Copyrighted</label><br>
          </div>
          <div class="flag-options-symbols">
            <button class="close-flag">X</button>
            <button class="report-btn">Report</button>
          </div>
        </div>
      </div>
    </div>

    <div class="second-article">
      <div class="second-article-content">
        <h3>Sky-diving in St. Martin </h3>
        <p class="meta">asif.iqbal69 | 05/03/2017 – 20:16:53 GMT</p>
        <p class="text">
          Skydiving has always been of interest to me as a natural consequence of my desire to fly. I have always wanted to ... 
        </p>
      </div>
      <div class="article-flagging">
        <button class="flag-btn">Flag</button>
        <div class="flag-options" style="display: none;">
          <div class="flag-options-content">
            <label><input type="checkbox"> Abusive</label><br>
            <label><input type="checkbox"> Spam</label><br>
            <label><input type="checkbox"> Copyrighted</label><br>
          </div>
          <div class="flag-options-symbols">
            <button class="close-flag">X</button>
            <button class="report-btn">Report</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- DYNAMIC ARTICLES FROM DB -->
  <div id="dynamic-articles"></div>

  <!-- POST NEW ARTICLE -->
  <div class="post-article">
    <div class="post-header">
      <label>Post a new article:</label>  
      <input type="text" id="article-title" placeholder="Enter your article title here">
      <p>Signed in as: <span id="signed-user">[username]</span></p>
    </div>

    <div class="post-body">
      <textarea id="article-text" placeholder="Type your article here"></textarea>
      <button id="post-btn" disabled>Post</button>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="script.js"></script>

</body>
</html>