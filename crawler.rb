require 'json'
require 'rubygems'
require 'nokogiri'
require 'open-uri'

# Fetching the page
source_url = 'http://www.radiolab.org/blogs/radiolab-blogland/'
page = Nokogiri::HTML(open(source_url))

# get article details of first in list (class="channel-item first")
title_first = page.css('div[class="channel-item first"]').css('h2').text
date_first = page.css('div[class="channel-item first"]').css('h3').text
tease_first = page.css('div[class="channel-item first"]').css('div[class="no-object"]').text
## author_first

# get article details of the rest (class="channel-item")
articles = page.css('div[class="channel-item"]')

articles.each do |article|
	title = article.css('h2').text.strip!
	date = article.css('h3').text.strip!
	tease = article.css('div[class="no-object"]').text.strip!

	# check if there is an author for the article
	if article.css('p[class="byline"]').any?
		author = article.css('p[class="byline"]').css('a').text
	else
		author = 'unknown'
	end

	# print our results
	puts title
	puts date
	puts tease
	puts author
	puts '---------------------------------------------'
end