<?php

/**
 * Fetch the contents of a URL with cURL
 *
 * @param string $url The URL to fetch
 * @param string $userAgent The user agent to use
 * @return string The contents of the URL
 */
function curlGetContents($url, $userAgent)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * Fetch an image, if it is larger than 4.5MB, redirect to it
 * otherwise, return the image as content
 *
 * @param string $url The URL to fetch
 * @param string $userAgent The user agent to use
 * @param bool $redirect Whether to force a redirect
 */
function displayImage($url, $userAgent, $redirect)
{
    // don't need to fetch the image if we're redirecting
    $contents = $redirect ? "" : curlGetContents($url, $userAgent);

    // redirect if redirect is set or the image is larger than 4.5MB
    if ($redirect || strlen($contents) > 4500000) {
        header("Location: $url");
        exit;
    }

    // set content type
    if (preg_match("/\.(jpg|jpeg)$/", $url)) {
        header('Content-Type: image/jpeg');
    } elseif (preg_match("/\.(png)$/", $url)) {
        header('Content-Type: image/png');
    } elseif (preg_match("/\.(gif)$/", $url)) {
        header('Content-Type: image/gif');
    }
    // set default filename
    header('Content-Disposition: inline; filename="' . basename($url) . '"');
    // output the image
    exit($contents);
}

$REPO = "gepolis/mwc";

$BRANCH_NAME = "main";

$IMAGES_DIRECTORY = "images";

$BASE_URL = "https://raw.githubusercontent.com/$REPO/$BRANCH_NAME/$IMAGES_DIRECTORY/";

// prefix for generating 332x200px thumbnails
$IMGPROXY_PREFIX = "https://dc1imgproxy.fly.dev/x/rs:auto:332:200:1/plain/" . urlencode($BASE_URL);

// API url to get a listing of images in the directory on GitHub
$GITHUB_API_URL = "http://gepolis.yzz.me/?random";

// whether to force a redirect to the image instead of displaying it directly
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] === "1" : false;

// if the current URL is in the form "/images/...", show the image
if (preg_match("/\/images\/(.*)$/", $_SERVER['REQUEST_URI'], $matches)) {
    $image_path = $BASE_URL . $matches[1];
    displayImage($image_path, $REPO, $redirect);
}

// fetch the list of images from GitHub
$images = json_decode(curlGetContents($GITHUB_API_URL, $REPO), true);

// if the random query string parameter is set, pick a random image
if (isset($_GET['random'])) {
    // get the image url
    $random_image_path = $images[array_rand($images)]["download_url"];
    displayImage($random_image_path, $REPO, $redirect);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Коллекция Минималистичных Обоев</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <!-- glightbox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Custom CSS -->
    <style>
        body {
            background: #1d1d1d;
            color: #fff;
            font-family: 'Poppins', 'Open Sans', Arial, Helvetica, sans-serif;
            text-align: center;
        }

        a {
            color: #fff;
        }

        .title {
            margin-top: 2em;
        }

        .footer {
            margin-top: 2em;
            color: #aaa;
        }

        .icons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2em;
            margin-top: 2em;
            margin-bottom: 2em;
        }

        .icons svg:hover {
            cursor: pointer;
            filter: drop-shadow(0px 0px 2px rgb(255 255 255 / 0.4))
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 1em;
            width: calc(100% - 2em);
            max-width: 3600px;
            margin: auto;
        }

        .gallery img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            min-height: 122px;
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            transition: 0.1s ease-in-out;
            position: relative;
        }

        .gallery img:hover {
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
        }

        .glightbox-clean .gslide-description {
            background: #1d1d1d
        }

        div.gslide-desc {
            color: #fff;
        }

        .gallery img.loading {
            aspect-ratio: 16 / 9;
            color: transparent;
            background: linear-gradient(0.25turn, transparent, #3c3c3c, transparent), linear-gradient(#2c2c2c, #2c2c2c);
            background-repeat: no-repeat;
            background-position: -315px 0, 0 0;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            to {
                background-position: 315px 0, 0 0;
            }
        }
    </style>
</head>

<body>
    <h1 class="title">Коллекция Минималистичных Обоев</h1>

    <div class="icons">
        <a href="/?random" target="_blank" title="Random Image">
            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="2em" xmlns="http://www.w3.org/2000/svg">
                <path d="M386.688 487.75l-119.236-55.423c-7.898-3.673-11.334-13.065-7.66-20.976l84.374-181.523c3.667-7.904 13.07-11.334 20.963-7.667l119.24 55.434c7.9 3.673 11.33 13.065 7.656 20.964l-84.37 181.524c-3.678 7.904-13.076 11.334-20.968 7.667zM98.95 467.945L19.79 284.09c-3.448-8.007.255-17.302 8.25-20.744l39.196-16.872 48.975 184.044c4.694 17.588 22.755 28.078 40.36 23.39l39.032-10.386-75.907 32.686c-8.007 3.443-17.296-.255-20.744-8.262zm33.89-41.86L81.362 232.638c-2.24-8.42 2.78-17.078 11.19-19.312l34.033-9.052-4.098 30.465c-2.422 18.036 10.224 34.652 28.285 37.087l79.828 10.758-32.497 109.467c-3.345 11.28-.37 22.948 6.866 31.18l-52.82 14.05c-8.42 2.24-17.07-2.77-19.31-11.196zm108.428-4.76l-16.02-4.76c-8.36-2.49-13.12-11.267-10.644-19.627l56.97-191.9c2.484-8.36 11.28-13.12 19.622-10.65l49.073 14.583.008-.005.12.044-.133-.034c-4.93 3.254-9.04 7.868-11.705 13.605l-84.38 181.53c-2.587 5.586-3.486 11.517-2.915 17.218zm-5.707-155.43l-82.486-11.117c-8.633-1.166-14.704-9.12-13.538-17.758l26.73-198.39c1.16-8.633 9.125-14.698 17.74-13.538l130.327 17.563c8.627 1.166 14.692 9.125 13.532 17.752L311.42 182.46l-15.33-4.552c-17.467-5.197-35.826 4.784-41.004 22.232l-19.525 65.755zm-5.19-31.46c4.67-3.055 7.474-7.438 8.42-13.145.936-5.633-.357-10.617-3.866-14.945-3.51-4.414-8.39-7.14-14.656-8.178-6.344-1.057-11.93-.073-16.75 2.956-4.826 3.03-7.692 7.316-8.615 12.87-.898 5.386.425 10.42 3.97 15.082 3.565 4.504 8.525 7.285 14.863 8.34 6.35 1.057 11.893.062 16.634-2.98zm25.978-81.243c4.693-2.726 8.888-5.434 12.598-8.117 3.703-2.684 6.915-5.586 9.635-8.725 2.72-3.13 4.967-6.573 6.733-10.307 1.76-3.74 3.048-8.032 3.85-12.865 1.262-7.62 1.02-14.358-.735-20.234-1.75-5.87-4.693-10.94-8.833-15.22-4.135-4.27-9.24-7.753-15.318-10.43-6.07-2.684-12.804-4.633-20.174-5.86-7.692-1.28-15.3-1.602-22.815-.977-7.516.614-14.63 2.247-21.346 4.88l-5.95 35.802c6.813-4.25 13.77-7.104 20.855-8.567 7.09-1.475 13.726-1.7 19.913-.668 21.467 4.092 19.44 24.898 8.76 34.03-5.652 4.473-11.334 8.802-15.942 11.345-10.48 5.914-27.69 23.125-22.542 45.145l31.284 5.202c-7.11-17.757 11.663-29.462 20.028-34.434z"></path>
            </svg>
        </a>
    </div>

    <div class="gallery">
        <?php foreach ($images as $image) : ?>
            <?php $image_path = $image["download_url"]; ?>
            <a href="<?= $image_path; ?>" class="glightbox" data-alt="<?= basename($image_path); ?>" data-description="<?= basename($image_path); ?>">
                <img src="<?= $IMGPROXY_PREFIX . basename($image_path); ?>" loading="lazy" alt="<?= basename($image_path); ?>" title="<?= basename($image_path); ?>" class="loading" onload="this.classList.remove('loading')">
            </a>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p>Сайт создан <a href="https://github.com/gepolis/">gepolis</a>, &copy; <?= date('Y'); ?></p>
    </div>

    <script type="text/javascript">
        window.addEventListener("load", function() {
            /**
             * Open image based on hash
             *
             * If the hash is "gallery", open the first image in the gallery,
             * otherwise, open the image with the hash as the alt attribute.
             */
            function openImageFromHash() {
                const hash = window.location.hash.substr(1);
                const query = hash === "gallery" ? ".gallery img" : `.gallery img[alt="${hash}"]`;
                const imageElement = document.querySelector(query);
                if (imageElement) {
                    imageElement.click();
                }
            }

            // initialize glightbox
            const lightbox = GLightbox();

            // add image dimensions to description on image load
            lightbox.on("slide_after_load", function(slide) {
                // get the dimensions of the slide image
                const image = slide.slide.querySelector("img");
                const width = image.naturalWidth;
                const height = image.naturalHeight;
                // get the description and add the dimensions if not already present
                const description = slide.slideConfig.description;
                const parts = description.split(" • ");
                parts[1] = ` ${width}x${height}`;
                slide.slideConfig.description = parts.join(" • ");
                // update the description
                slide.slide.querySelector(".gslide-desc").innerText = slide.slideConfig.description;
            });

            // add hash to url when image is opened
            lightbox.on("slide_changed", function(slide) {
                window.location.hash = slide.current.slideConfig.alt;
            });

            // remove hash from url when image is closed
            lightbox.on("close", function() {
                history.replaceState(null, null, window.location.pathname);
            });

            // if hash is set, open image
            if (window.location.hash) {
                openImageFromHash();
            }

            // if hash is changed and lightbox is closed, open image
            window.addEventListener("hashchange", function() {
                if (!lightbox.lightboxOpen) {
                    openImageFromHash();
                }
            });
        });

        // if imgproxy version fails to load, fallback to full-size image
        document.querySelectorAll(".gallery img").forEach(function(img) {
            img.addEventListener("error", function() {
                this.src = this.parentElement.href;
            });
        });
    </script>
</body>

</html>