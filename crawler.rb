require 'json'
require 'rubygems'
require 'nokogiri'
require 'open-uri'
require 'mysql'

# functions file
require_relative 'function_crawler.rb'

# Fetching the page
base_url = 'http://www.radiolab.org/blogs/radiolab-blogland/'
page_url = ''

nextpage_exists = true
page_number = 1

# connecting to mysql
begin
    con = Mysql.new 'localhost', 'XXXX', 'XXXX'

    # goes through all pages of website
	while nextpage_exists

		page = Nokogiri::HTML(open(base_url+page_url))

		# first article in list has its own html class ("channel-item first"))
		title = get_article(page.css('div[class="channel-item first"]'), con)
		get_content(page.css('div[class="channel-item first"]'), title, con)

		# get details for the rest of the article
		articles = page.css('div[class="channel-item"]') ####
		# articles = page.css('div[class="channel-item"]').slice(0..0)

		articles.each do |article|			
			title = get_article(article, con)
			get_content(article, title, con)
		end

		page_number += 1
		page_url = page_number.to_s + '/'

		nextpage_exists = page.css('span[class="pagefooter-next"]').any?  ####
		# nextpage_exists = false

	end

# catch errors with sql database
rescue Mysql::Error => e
    puts e.errno
    puts e.error

# close mysql connection
ensure
    con.close if con
end