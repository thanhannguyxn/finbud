// Configure AWS SDK
const AWS_REGION = "ap-southeast-2"; // Replace with your bucket's region (e.g., ap-southeast-2)
const S3_BUCKET_NAME = "financial-goals-bucket"; // Replace with your bucket name
const AWS_ACCESS_KEY_ID = "AKIA23WHUKCW25UQ3R6L"; // Replace YOUR_ACCESS_KEY with your Access Key ID
const AWS_SECRET_ACCESS_KEY = "VL0d3s/NdzMZs1eIShi8QcCQqV1hbITaKZwrasP1"; // Replace YOUR_SECRET_KEY with your Secret Access Key

// Add event listener for the "Share on Facebook" button
document
.getElementById("shareOnFacebook")
.addEventListener("click", function () {
    const financialGoalsElement = document.querySelector(
        ".financial-goals-card"
    );
    
    // Use html2canvas to capture the element
    html2canvas(financialGoalsElement)
    .then((canvas) => {
        // Convert canvas to Blob
        canvas.toBlob((blob) => {
            const fileName = `financial-goal-${Date.now()}.png`; // File name on S3
            
            // Initialize AWS SDK
            const s3 = new AWS.S3({
                region: AWS_REGION,
                credentials: {
                    accessKeyId: AWS_ACCESS_KEY_ID,
                    secretAccessKey: AWS_SECRET_ACCESS_KEY,
                },
            });
            
            // Configure upload parameters for S3
            const params = {
                Bucket: S3_BUCKET_NAME,
                Key: fileName,
                Body: blob,
                ContentType: "image/png",
            };
            
            // Upload the image to S3
            s3.upload(params, (err, data) => {
                if (err) {
                    console.error("Error uploading to S3:", err);
                    alert("Failed to upload the image to S3. Please try again.");
                } else {
                    console.log("Image uploaded successfully:", data.Location);
                    
                    // Open Facebook Share Dialog with the image URL from S3
                    const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
                        data.Location
                    )}`;
                    window.open(facebookShareUrl, "_blank");
                }
            });
        }, "image/png");
    })
    .catch((error) => {
        console.error("Error capturing financial goals:", error);
        alert("Failed to capture the financial goals. Please try again.");
    });
});
