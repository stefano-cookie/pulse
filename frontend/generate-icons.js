import sharp from 'sharp';

const sizes = [192, 512];
const svgContent = `<svg width="512" height="512" viewBox="0 0 512 512" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="512" height="512" rx="100" fill="#4f46e5"/>
  <circle cx="256" cy="180" r="60" fill="white"/>
  <path d="M256 260c-80 0-160 40-160 100v32h320v-32c0-60-80-100-160-100z" fill="white"/>
  <circle cx="380" cy="140" r="40" fill="#ef4444"/>
</svg>`;

async function generateIcons() {
  for (const size of sizes) {
    await sharp(Buffer.from(svgContent))
      .resize(size, size)
      .png()
      .toFile(`public/pwa-${size}x${size}.png`);
    console.log(`Generated pwa-${size}x${size}.png`);
  }
}

generateIcons();
