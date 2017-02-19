# aceList: Curate your A-List

aceList is a self-contained subscription list management system using PHP, SQLite3 and jQuery (with a keen focus on UX) :sparkles:

[![aceList](https://raw.githubusercontent.com/thinkdj/aceList/master/aceList/hero.png "aceList: Independent Self-hosted Subscription List Management system using PHP/SQLite/jQuery")](http://think.dj/projects/aceList)

## Sweet Features

- Export your subscriber list as CSV and import them with ease in the marketing tool of your choice (AWeber, Amazon SES, MailChimp, SendGrid Email Marketing, Campaign Monitor...)
- Uses SQLite (file-based) as the datastore so that you can get going without MySQL
- Maximum compatibility with your existing web app / homepage - All design and code starts with aceList* 
- No external dependencies - New to git? No problem. Don't know what stuff like composer/npm is about? No worries (You should check them out, though). Download the code, upload it to your server and get going. That's it! 

## Requirements

1. PHP Server with sqlite3 extension (enabled by default on PHP 5.3.0 and above)
2. Access to 1.

## Getting Started
- Download the archive, extract it
- Edit `./aceListConfig.php` and make required changes (Admin Password, Language etc)
- Edit `index.php` to make it your own 
- If required, edit `./aceList/locale/{lang}.php` to change UI messages 
- Upload all files to your server and make sure the db file is writable (default: `aceListSubscribers.db`)

## Admin
- Admin Panel can be accessed via `./aceList.php?admin`
- Login with the password (set in aceListConfig.php) to download your aceList

PS: You can use a tool like [DB Browser](http://sqlitebrowser.org/) to view the SQLite3 database

### Todo

- Double Opt-in
- Manage list via Admin Panel 

------

:large_blue_circle: Follow me [@thinkdj](https://twitter.com/thinkdj) 