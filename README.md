# C23 Blogs

A simple, modern, and high-performance WordPress plugin to publish and showcase blog posts. Packed with responsive card layouts, dynamic archive headers, typography controls, estimated reading times, author bio boxes, and an intuitive tabbed settings dashboard.

---

## ✨ Features

- **Responsive Card Layouts**:
  - Choose between **Grid** (2, 3, or 4 columns) or **List** (horizontal cards) layouts.
  - Subtle image hover zoom animation without layout jitter.
  - Full-width modern card designs.

- **Dynamic Archive Header & Permalinks**:
  - Configurable archive page **Title** and **Subtitle / Description**.
  - Customizable archive **URL Slug** (default: `/blogs`, e.g., `/articles`, `/news`) with automatic rewrite rule flushing on save.

- **Typography & Font Customization**:
  - **Headings / Post Titles**: Font family (System or Google Fonts: Inter, Outfit, Montserrat, Playfair Display, Merriweather, Roboto, Georgia), font weight, card title size, and single post title size.
  - **Body / Excerpt**: Font family, font size, and line height.
  - Automatic on-demand Google Fonts stylesheet loading.

- **Reader Experience & Metadata**:
  - Automated estimated reading time calculation.
  - Author avatars, publication dates, and category pills.
  - Author bio box with avatar and biographical info on single post view.
  - Adjacent post navigation (Previous / Next post links).
  - Optional comments toggle for single blog views.

- **Distributed & Organized Settings Panel**:
  - Clean, full-page admin interface categorized into 5 focused tabs:
    1. **Archive & Layout**: Page title/subtitle, URL slug, grid/list view, columns, posts per page, and excerpt length.
    2. **Single Post**: Author bio box, post navigation, and comments toggle.
    3. **Metadata & Badges**: Reading time, author meta, date, and category badges.
    4. **Typography**: Post title fonts/sizes and body text fonts/line heights.
    5. **Colors & Styling**: Color pickers for primary accent, headings, body text, card background, border radius, and card shadow depth.

- **Theme Compatibility**:
  - 100% compatible with modern **Full Site Editing (FSE) Block Themes** (Twenty Twenty-Five, Twenty Twenty-Four) as well as classic PHP themes.
  - Standardized OOP architecture with safe `c23_blogs` post type preservation for database stability.

---

## 📁 Architecture Overview

```text
c23-blogs/
├── c23-blogs.php                     # Main plugin entry point & bootstrap
├── archive-c23_blogs.php             # Archive template fallback redirect
├── assets/
│   ├── css/
│   │   ├── admin.css                 # Settings panel full-page styling
│   │   └── frontend.css              # Modern CSS variables, cards, single layouts
│   └── js/
│       └── admin.js                  # Settings tab navigation & layout toggles
├── includes/
│   ├── class-c23-blogs.php           # Core singleton manager
│   ├── class-c23-blogs-post-type.php # Post type ('c23_blogs') & taxonomies
│   ├── class-c23-blogs-settings.php  # Admin settings dashboard & sanitization
│   ├── class-c23-blogs-templates.php # Template routing & component renderers
│   ├── class-c23-blogs-frontend.php  # Asset enqueueing & inline dynamic CSS
│   └── class-c23-blogs-widget.php    # Sidebar widget integration
├── templates/
│   ├── archive.php                   # Blog archive page template
│   ├── single.php                    # Single blog post template
│   └── partials/
│       ├── card-grid.php             # Grid card component
│       └── card-list.php             # List card component
└── widgets/
    └── c23-blogs-list-widget.php     # Legacy widget compatibility wrapper
```

---

## 🚀 Installation

1. Copy or clone the `c23-blogs` directory into your WordPress installation:
   ```bash
   wp-content/plugins/c23-blogs
   ```
2. Navigate to **Plugins** in the WordPress Admin Dashboard.
3. Locate **C23 Blogs** and click **Activate**.
4. Access plugin settings under **Blogs → Settings** in the WordPress admin menu.

---

## ⚙️ Configuration

1. In the WordPress Admin, go to **Blogs → Settings**.
2. **Archive & Layout**:
   - Set your archive page title and subtitle.
   - Configure your desired URL slug (e.g. `blogs`).
   - Select your preferred layout (Grid or List) and column count.
3. **Typography**:
   - Choose fonts, sizes, and line-heights for titles and body text.
4. **Colors & Styling**:
   - Customize your theme accent color, text colors, card radius, and drop shadow.
5. Click **Save Changes** (floating bar or header button).

---

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).
