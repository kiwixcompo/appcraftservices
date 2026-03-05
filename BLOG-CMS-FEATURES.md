# Blog CMS - Complete Feature Guide

## ✅ Implemented Features

### 1. Automatic Sitemap Updates
- **Auto-regeneration**: Sitemap.xml automatically updates when you publish, edit, or delete blog posts
- **Google-friendly**: Proper XML format with priorities and change frequencies
- **Includes all published posts**: Only published posts appear in sitemap
- **SEO optimized**: Helps Google discover and index your blog posts faster

### 2. Markdown Preview
- **Live Preview Button**: Click "Preview" to see how your post will look
- **Full Rendering**: Shows title, category, tags, featured image, and formatted content
- **Markdown Support**: Properly renders all markdown formatting
- **Responsive Preview**: See exactly how it will appear on the live site

### 3. Bulk Markdown Upload
- **Multiple Files**: Upload multiple .md files at once
- **Auto-parsing**: Automatically extracts title, content, and metadata
- **Frontmatter Support**: Reads YAML frontmatter for metadata
- **Confirmation Dialog**: Review all posts before importing
- **Selective Import**: Choose which posts to import

#### Markdown File Format:
```markdown
---
title: Your Blog Post Title
category: Startup Development
tags: [MVP, Startup, Web Development]
excerpt: Brief summary of your post
author: Williams Alfred Onen
featured: false
---

# Your Blog Post Title

Your content here with full markdown support...

## Heading 2
### Heading 3

**Bold text** and *italic text*

- Bullet points
- Another point

1. Numbered lists
2. Another item

[Links](https://example.com)

\`inline code\` and code blocks:

\`\`\`
code block here
\`\`\`
```

### 4. Post Scheduling
- **Schedule for Later**: Set specific date and time for auto-publishing
- **Bulk Scheduling**: Schedule multiple posts (1 per day automatically)
- **Auto-publish**: Posts automatically go live at scheduled time
- **Draft Mode**: Save posts as drafts for later editing

## 📝 How to Use

### Creating a Single Post

1. **Go to Admin Dashboard** → Click "Blog Posts"
2. **Click "Add New Post"**
3. **Fill in the form**:
   - Title (auto-generates slug)
   - Category
   - Excerpt (summary)
   - Content (Markdown supported)
   - Tags (comma-separated)
   - Featured image (upload or paste)

4. **Choose Publishing Option**:
   - **Publish immediately**: Goes live right away
   - **Save as draft**: Saves for later
   - **Schedule for later**: Set date/time for auto-publish

5. **Preview** (optional): Click "Preview" to see how it looks
6. **Save Post**: Click "Save Post"

### Bulk Uploading Markdown Files

1. **Prepare your .md files** with proper frontmatter (see format above)
2. **Click "Upload .md Files"** in the blog post form
3. **Select multiple files** from your computer
4. **Review the confirmation dialog**:
   - See all posts that will be imported
   - Uncheck any you don't want to import
   - Choose publishing option:
     - Publish all now
     - Save all as drafts
     - Schedule (1 per day starting tomorrow)
5. **Click "Import Posts"**

### Scheduling Posts

#### Single Post:
1. Select "Schedule for later" radio button
2. Choose date and time
3. Save post
4. Post will automatically publish at that time

#### Bulk Posts:
1. Upload multiple .md files
2. Select "Schedule (1 per day)" option
3. Posts will be scheduled starting tomorrow at 9 AM, one per day

### Setting Up Auto-Publishing (Cron Job)

For scheduled posts to automatically publish, set up a cron job:

#### Option 1: Server Cron (Recommended)
Add this to your crontab:
```bash
0 * * * * php /path/to/admin/api/publish_scheduled_posts.php
```

#### Option 2: Web Cron (Easier)
Use a service like cron-job.org or EasyCron:
```
URL: https://appcraftservices.com/admin/api/publish_scheduled_posts.php?key=appcraftservices2026
Frequency: Every hour
```

**Security Note**: Change the secret key in `publish_scheduled_posts.php` to something secure!

## 🎨 Markdown Formatting Guide

### Headers
```markdown
# H1 Heading
## H2 Heading
### H3 Heading
```

### Text Formatting
```markdown
**Bold text**
*Italic text*
***Bold and italic***
```

### Links
```markdown
[Link text](https://example.com)
```

### Lists
```markdown
- Bullet point
- Another point

1. Numbered item
2. Another item
```

### Code
```markdown
Inline `code` here

\`\`\`
Code block
Multiple lines
\`\`\`
```

### Images
```markdown
![Alt text](image-url.jpg)
```

## 📊 Blog Post Status

- **Published**: Live on website, visible to visitors, in sitemap
- **Draft**: Saved but not published, only visible in admin
- **Scheduled**: Will auto-publish at specified date/time

## 🔄 Sitemap Auto-Update

The sitemap automatically updates when you:
- ✅ Publish a new post
- ✅ Update an existing post
- ✅ Delete a post
- ✅ Scheduled post goes live

**Sitemap Location**: https://appcraftservices.com/sitemap.xml

## 🖼️ Image Management

### Featured Images
- **Upload**: Click "Upload Image" button
- **Paste**: Click paste area and Ctrl+V
- **Auto-conversion**: All images converted to WebP
- **Optimization**: 85% quality for best size/quality balance

### Images in Content
Use markdown syntax:
```markdown
![Image description](assets/blog/image.webp)
```

## 📱 Blog Post URLs

Clean, SEO-friendly URLs:
```
https://appcraftservices.com/blog/your-post-slug
```

## 🔍 SEO Features

- ✅ Clean URLs with slugs
- ✅ Meta descriptions from excerpts
- ✅ Open Graph tags for social sharing
- ✅ Twitter Card support
- ✅ Automatic sitemap inclusion
- ✅ Proper heading hierarchy
- ✅ Image alt tags
- ✅ Canonical URLs

## 📈 Best Practices

### Writing Posts
1. **Clear titles**: Descriptive and keyword-rich
2. **Good excerpts**: 150-200 characters, compelling summary
3. **Use headings**: Break content into sections with H2/H3
4. **Add images**: Featured image + images in content
5. **Tag appropriately**: 3-5 relevant tags
6. **Proofread**: Use preview before publishing

### Scheduling Strategy
1. **Consistency**: Schedule posts at same time/day
2. **Frequency**: 1-2 posts per week is good
3. **Timing**: 9 AM on weekdays works well
4. **Batch creation**: Write multiple posts, schedule them out

### SEO Optimization
1. **Keywords**: Include in title, excerpt, and content
2. **Internal links**: Link to other pages on your site
3. **External links**: Link to authoritative sources
4. **Alt text**: Describe images for accessibility and SEO
5. **Categories**: Use consistent, descriptive categories

## 🛠️ Troubleshooting

### Scheduled Posts Not Publishing
- Check cron job is set up correctly
- Verify secret key matches in cron URL
- Check server time zone settings
- Manually trigger: visit the cron URL in browser

### Sitemap Not Updating
- Check file permissions on sitemap.xml
- Verify admin is logged in when saving posts
- Manually regenerate from admin dashboard

### Images Not Uploading
- Check assets/blog/ folder exists and is writable
- Verify file size is under server limit
- Check browser console for errors

### Preview Not Working
- Ensure all required fields are filled
- Check browser console for JavaScript errors
- Try refreshing the page

## 📞 Support

For issues or questions about the blog CMS, check:
1. Browser console for errors
2. Server error logs
3. File permissions on data/ and assets/ folders

## 🎯 Quick Reference

| Action | Shortcut |
|--------|----------|
| Create post | Admin → Blog Posts → Add New Post |
| Preview | Fill form → Click Preview button |
| Bulk upload | Add New Post → Upload .md Files |
| Schedule | Select "Schedule for later" → Set date/time |
| Edit post | Blog list → Click edit icon |
| Delete post | Blog list → Click delete icon |

---

**Status**: ✅ All features fully implemented and ready to use!
