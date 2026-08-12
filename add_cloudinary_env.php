<?php

/**
 * Add Cloudinary environment variables to .env
 */
$envPath = __DIR__.'/../.env';
$envContent = file_get_contents($envPath);

$cloudinaryVars = '
# Cloudinary
CLOUDINARY_URL=cloudinary://126955573465339:eqyYD61cKKxTyUn9adMAVbHo7Xk@dkf6fhi4m
CLOUDINARY_CLOUD_NAME=dkf6fhi4m
CLOUDINARY_API_KEY=126955573465339
CLOUDINARY_API_SECRET=eqyYD61cKKxTyUn9adMAVbHo7Xk
';

if (strpos($envContent, 'CLOUDINARY_URL') === false) {
    file_put_contents($envPath, $envContent.$cloudinaryVars);
    echo 'Cloudinary credentials added to .env!';
} else {
    echo 'Cloudinary credentials already exist in .env';
}
