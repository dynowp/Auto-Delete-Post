# Auto Delete Post - WordPress Plugin

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.3%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-2.0.0-orange.svg)]()

🚀 **A comprehensive WordPress plugin that provides an administrative interface for configuring automated deletion of posts, taxonomies, comments, and categories with advanced filtering options and comprehensive logging system.**

## ✨ Features

### 🎯 **Core Functionality**
- **Automated Post Deletion** - Bulk delete posts with customizable limits
- **Comment Management** - Automated comment cleanup
- **Category & Tag Cleanup** - Remove unused taxonomies
- **Attachment Handling** - Option to delete associated media files
- **Flexible Scheduling** - Multiple cron interval options

### 🔧 **Advanced Filtering**
- **Date Range Filtering** - Include/exclude posts by date range
- **User-based Filtering** - Target specific authors or exclude them
- **Status-based Selection** - Filter by post status
- **Batch Processing** - Configurable limits per execution

### 📊 **Monitoring & Logging**
- **Comprehensive Logging System** - Track all deletion activities
- **Statistics Dashboard** - Monitor deletion counts and execution history
- **Real-time Status** - View current process status
- **Error Tracking** - Detailed error reporting and handling

### ⚙️ **Configuration Options**
- **Master Enable/Disable Switch** - Global control over all operations
- **Granular Controls** - Enable/disable specific deletion types
- **Safety Features** - Confirmation prompts and warnings
- **Flexible Scheduling** - From every minute to daily intervals

## 🛠️ Installation

### Method 1: WordPress Admin Dashboard
1. Download the plugin ZIP file
2. Navigate to **Plugins > Add New** in your WordPress admin
3. Click **Upload Plugin** and select the ZIP file
4. Click **Install Now** and then **Activate**

### Method 2: Manual Installation
1. Download and extract the plugin files
2. Upload the `auto-delete-post` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## 📸 Screenshots

### Settings Tab
<img width="1224" height="1210" alt="auto-delete-post-01" src="https://github.com/user-attachments/assets/70591c65-29c6-482e-848f-5da35c3567b2"/>

*Configure all deletion settings including posts, comments, categories, and scheduling options*

### Status & Statistics Tab
<img width="1224" height="659" alt="auto-delete-post-02" src="https://github.com/user-attachments/assets/4b3a1709-0de0-453d-ab95-36d4c52923af" />

*Monitor deletion statistics, execution history, and current process status*

### Logs Tab
<img width="1224" height="710" alt="auto-delete-post-03" src="https://github.com/user-attachments/assets/88b5a939-c567-4934-af9b-bcc447ab0027" />

*View detailed logs of all deletion activities, errors, and system messages*

## 🚀 Quick Start

1. **Navigate to Settings**: Go to **Tools > Auto Delete Post Settings**
2. **Enable the Plugin**: Toggle the "Enable Deletion Process" switch
3. **Configure Post Settings**: Set limits and enable post deletion
4. **Set Schedule**: Choose your preferred cron interval
5. **Save Settings**: Click "Save Changes" to activate

## 📋 Configuration Guide

### Global Settings
- **Enable Deletion Process**: Master switch for all deletion operations
- **Cron Interval**: Choose from multiple scheduling options:
  - Every Minute
  - Every 5 Minutes
  - Every 15 Minutes
  - Hourly
  - Daily

### Post Deletion Settings
- **Posts per Execution**: Set batch size (1-1000)
- **Delete Attachments**: ⚠️ Permanently removes associated media files
- **Date Filter**: Include/exclude posts from specific date ranges
- **User Filter**: Target or exclude specific authors

### Comment Settings
- **Comments per Execution**: Set batch size (1-500)
- **Status Filter**: Target specific comment statuses

### Category & Tag Settings
- **Deletion Limits**: Control batch sizes
- **Empty Only Option**: Delete only unused categories/tags

## 📊 Monitoring & Statistics

The plugin provides comprehensive monitoring through three main tabs:

### 📈 Statistics Dashboard
- Total posts deleted
- Total comments deleted
- Total categories/tags deleted
- Execution history
- Last execution timestamp

### 📝 Activity Logs
- Real-time deletion logs
- Error tracking
- Success confirmations
- Detailed operation history

### ⚡ Status Monitor
- Current process status
- Next scheduled execution
- Active filters summary
- System health indicators

## ⚠️ Important Safety Notes

### 🔒 **Data Safety**
- **ALWAYS backup your database** before enabling automated deletion
- Test settings on a staging environment first
- Start with small batch sizes to monitor impact
- Review logs regularly to ensure expected behavior

### 🚨 **Attachment Deletion Warning**
Enabling "Delete Attachments" will **permanently remove all media files** associated with deleted posts. This action cannot be undone.

## 🔧 Technical Requirements

- **WordPress**: 6.3 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Memory**: 128MB minimum (256MB recommended)

## 🐛 Troubleshooting

### Common Issues

**Plugin not deleting posts:**
- Verify the master switch is enabled
- Check that post deletion is enabled
- Ensure cron jobs are working on your server
- Review error logs for specific issues

**Cron jobs not running:**
- Check if WordPress cron is disabled
- Verify server cron configuration
- Test with shorter intervals first

**Memory issues:**
- Reduce batch sizes
- Increase PHP memory limit
- Process smaller chunks at a time

## 🤝 Contributing

We welcome contributions! Please feel free to submit issues, feature requests, or pull requests.

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html).

## 👨‍💻 Author

**DynoWP**
- Website: [https://dynowp.com](https://dynowp.com)

## 🔄 Changelog

### Version 2.0.0
- ✨ Complete rewrite with modern architecture
- 🎯 Advanced filtering options (date and user-based)
- 📊 Enhanced logging and statistics system
- 🔧 Improved admin interface with tabbed navigation
- ⚡ Better performance and error handling
- 🛡️ Enhanced security measures

---

**🚨 Remember: Always backup your data before using automated deletion tools!**