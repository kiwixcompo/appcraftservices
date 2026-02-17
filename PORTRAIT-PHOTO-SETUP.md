# Portrait Photo Setup Guide

## Overview
The homepage hero section now includes a professional portrait photo display on the right side (desktop) or below the text (mobile).

## Photo Placement

### File Location
Place your portrait photo at: `assets/portrait-photo.webp`

### Recommended Specifications

**Image Requirements:**
- **Format**: WebP (modern, optimized format with better compression)
- **Dimensions**: 800x800 pixels (square, 1:1 aspect ratio)
- **File Size**: Under 200KB for optimal loading (WebP provides excellent compression)
- **Quality**: High resolution for retina displays
- **Background**: Professional background or transparent WebP

**Photo Guidelines:**
- Professional headshot or upper body shot
- Good lighting with clear facial features
- Neutral or professional background
- Centered composition
- Friendly, approachable expression
- Professional attire

### Optimal Photo Composition
```
┌─────────────────┐
│                 │
│   [Your Face]   │  ← Face should be centered
│                 │
│  [Upper Body]   │  ← Include shoulders/upper torso
│                 │
└─────────────────┘
```

## Current Implementation

### Desktop View (Large Screens)
- Photo appears on the RIGHT side of the hero section
- Text content on the LEFT side
- Photo size: 384x384px (24rem)
- Circular frame with white border
- Decorative glow effect behind photo
- "Available for Projects" badge at bottom right

### Tablet View (Medium Screens)
- Photo appears on the RIGHT side
- Slightly smaller: 320x320px (20rem)
- Text remains on the LEFT
- Maintains circular frame and effects

### Mobile View (Small Screens)
- Photo appears BELOW the text content
- Size: 256x256px (16rem)
- Centered on screen
- Full effects maintained

## Design Features

### 1. Circular Frame
- Clean, professional circular crop
- 4px white border for contrast
- Shadow effect for depth

### 2. Decorative Background
- Subtle blue gradient glow
- Creates visual interest
- Doesn't overpower the photo

### 3. Status Badge
- "Available for Projects" indicator
- Green pulsing dot for attention
- White background for readability
- Positioned at bottom right of photo

### 4. Fallback Display
If the photo file is missing, a placeholder will show:
- Blue circular background
- White initials "WO" (Williams Onen)
- Professional appearance maintained

## How to Add Your Photo

### Step 1: Prepare Your Photo
1. Take or select a professional photo
2. Crop to square (1:1 aspect ratio)
3. Resize to 800x800 pixels
4. Convert to WebP format (use online converters or image editing tools)
5. Optimize file size (WebP provides excellent compression automatically)
6. Save as `portrait-photo.webp`

### Step 2: Upload to Website
1. Place file in the `assets` folder
2. Name it exactly: `portrait-photo.webp`
3. The website will automatically display it

### Step 3: Verify Display
1. Visit the homepage
2. Check on desktop, tablet, and mobile
3. Ensure photo is clear and well-positioned
4. Verify the "Available for Projects" badge is visible

## Customization Options

### Change Photo Size
Edit the classes in `index.html`:
```html
<!-- Current sizes -->
w-64 h-64      <!-- Mobile: 256x256px -->
md:w-80 md:h-80  <!-- Tablet: 320x320px -->
lg:w-96 lg:h-96  <!-- Desktop: 384x384px -->

<!-- To make larger, use: -->
w-72 h-72      <!-- Mobile: 288x288px -->
md:w-96 md:h-96  <!-- Tablet: 384x384px -->
lg:w-[28rem] lg:h-[28rem]  <!-- Desktop: 448x448px -->
```

### Change Badge Text
Edit the badge content in `index.html`:
```html
<span class="text-sm font-semibold text-navy">Available for Projects</span>
```

Change to:
- "Open to Opportunities"
- "Accepting New Clients"
- "Let's Build Together"
- Or remove the badge entirely

### Change Border Color
Edit the border class:
```html
border-4 border-white  <!-- Current: white border -->
border-4 border-electric-blue  <!-- Blue border -->
border-4 border-gray-200  <!-- Light gray border -->
```

### Remove Glow Effect
Remove or comment out this div:
```html
<div class="absolute -inset-4 bg-gradient-to-r from-electric-blue to-blue-400 rounded-full opacity-20 blur-2xl"></div>
```

## Responsive Behavior

### Large Screens (1024px+)
```
┌─────────────────────────────────────┐
│  [Text Content]    [Portrait Photo] │
│  Headline          ┌─────────┐      │
│  Subheadline       │         │      │
│  Buttons           │  Photo  │      │
│  Features          │         │      │
│                    └─────────┘      │
└─────────────────────────────────────┘
```

### Medium Screens (768px - 1023px)
```
┌─────────────────────────────────────┐
│  [Text Content]    [Portrait Photo] │
│  Headline          ┌────────┐       │
│  Subheadline       │ Photo  │       │
│  Buttons           └────────┘       │
│  Features                           │
└─────────────────────────────────────┘
```

### Small Screens (< 768px)
```
┌─────────────────┐
│  [Text Content] │
│  Headline       │
│  Subheadline    │
│  Buttons        │
│  Features       │
│                 │
│ [Portrait Photo]│
│   ┌────────┐    │
│   │ Photo  │    │
│   └────────┘    │
└─────────────────┘
```

## SEO Optimization

The portrait photo includes proper alt text:
```html
alt="Williams Alfred Onen - Startup Web Developer"
```

This helps with:
- Image search rankings
- Accessibility for screen readers
- Context for search engines
- Professional branding

## Performance Optimization

### Image Loading
- Uses `loading="eager"` for immediate display
- Fallback SVG placeholder for missing images
- Optimized file size recommendations
- Proper image dimensions prevent layout shift

### Best Practices
1. Use WebP format for optimal compression and quality
2. Ensure file size is under 200KB (WebP provides excellent compression)
3. Test loading speed after upload
4. WebP is supported by all modern browsers

## Troubleshooting

### Photo Not Showing
1. Check file name is exactly: `portrait-photo.webp`
2. Verify file is in `assets` folder
3. Clear browser cache and refresh
4. Check browser console for errors
5. Ensure your browser supports WebP (all modern browsers do)

### Photo Looks Distorted
1. Ensure original photo is square (1:1 ratio)
2. Use 800x800px or larger
3. Don't use stretched or compressed images

### Photo Too Large (File Size)
1. WebP format already provides excellent compression
2. Reduce dimensions to 800x800px if needed
3. Adjust quality settings (80-90% is good for WebP)
4. Use online WebP optimizers for further compression

### Badge Not Visible
1. Check if photo has dark bottom-right area
2. Consider adjusting badge position
3. Increase badge shadow for contrast

## Status: ✅ READY - WebP Format

The portrait photo section is now implemented and ready for your photo. Simply add `portrait-photo.webp` to the `assets` folder and it will display automatically with all the professional styling and effects.

### Why WebP?
- **Better Compression**: 25-35% smaller file sizes compared to PNG/JPG
- **Faster Loading**: Smaller files mean faster page load times
- **Better SEO**: Google favors faster-loading pages
- **Universal Support**: All modern browsers support WebP
- **Quality**: Maintains excellent image quality at smaller file sizes
