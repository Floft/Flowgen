flowgen
=======
This is an *old* script to generate a static HTML website from text files.

### Current Status
**WARNING:** I no longer use this script for many reasons:

 * It is slow
 * It is a hack
 * It is hard to change and extend

Thus, if for some reason somebody actually decided to use this, please use the
`./convertToJekyll.sh` script to salvage your website and convert it into
Jekyll-compatible HTML files. Something like this:

    ./convertToJekyll.sh flowgen/pages jekyll/

### Directories
But, if you do want to look at this thing, there's really two parts. The script
and then a website crawler for search indexing.

 * flowgen -- a *slow* script to create a static HTML website from txt files
 * spider -- a *slow* spider to crawl my website for a search database
