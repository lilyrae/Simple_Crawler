require 'json'
require 'rubygems'
require 'nokogiri'
require 'open-uri'
require 'mysql'

# Fetching the page
base_url = 'http://www.radiolab.org/blogs/radiolab-blogland/'
page_url = ''

nextpage_exists = true
page_number = 1

# connecting to mysql
begin
    con = Mysql.new 'localhost', 'XXXXXXX', 'XXXXXXX'

	while nextpage_exists

		page = Nokogiri::HTML(open(base_url+page_url))

		# first article in list has its own html class ("channel-item first"))
		# get article details of first in list
		title = page.css('div[class="channel-item first"]').css('h2').text.strip!
		date = page.css('div[class="channel-item first"]').css('h3').text.strip!
		tease = page.css('div[class="channel-item first"]').css('div[class="no-object"]').text.strip!
		source = 'radiolab'

		# check if there is an author for the first article
		if page.css('div[class="channel-item first"]').css('p[class="byline"]').any?
			author = page.css('div[class="channel-item first"]').css('p[class="byline"]').css('a').text
		else
			author = 'unknown'
		end

		# replace ' with '' in strings to enable sql query
		title.gsub! "'", "''"
		tease.gsub! "'", "''"
		#author.sub! "'", "''"

		# remove &nbsp; from string
		#title.sub! "&nbsp;", " "
		#tease.sub! "&nbsp;", " "
		#author.sub! "&nbsp;", " "

		# print out result
		# puts title

		# update sql database
		query = con.query "INSERT INTO  `simple_crawler`.`Article_Summary` (`ID`,`Title`,`Article_Date`,`Author`,`Tease`,`Source`,`Updated`)
				VALUES (NULL,'#{title}','#{date}','#{author}','#{tease}','#{source}',CURRENT_TIMESTAMP)
				ON DUPLICATE KEY UPDATE Tease='#{tease}', Updated=CURRENT_TIMESTAMP"

		# get article details for the rest (class="channel-item")
		articles = page.css('div[class="channel-item"]')

		articles.each do |article|
			title = article.css('h2').text.strip!
			date = article.css('h3').text.strip!
			tease = article.css('div[class="no-object"]').text.strip!
			source = 'radiolab'

			# check if there is an author for the article
			if article.css('p[class="byline"]').any?
				author = article.css('p[class="byline"]').css('a').text
				puts author
			else
				author = 'unknown'
			end
			
			# replace ' with '' in fields to enable sql query
			title.gsub! "'", "''"
			tease.gsub! "'", "''"
			
			# remove &nbsp; from fields
			#title.gsub! "&nbsp;", " "
			#tease.gsub! "&nbsp;", " "

			puts title
			
			# update sql database
			query = con.query "INSERT INTO  `simple_crawler`.`Article_Summary` (`ID`,`Title`,`Article_Date`,`Author`,`Tease`,`Source`,`Updated`)
					VALUES (NULL,'#{title}','#{date}','#{author}','#{tease}','#{source}',CURRENT_TIMESTAMP)
					ON DUPLICATE KEY UPDATE Tease='#{tease}', Updated=CURRENT_TIMESTAMP"

			# print our results
			# puts title
			#puts date
			#puts tease
			#puts author
			#puts '---------------------------------------------'
		end

		page_number += 1
		page_url = page_number.to_s + '/'

		nextpage_exists = page.css('span[class="pagefooter-next"]').any?

	end

# catch errors with sql database
rescue Mysql::Error => e
    puts e.errno
    puts e.error

# close mysql connection
ensure
    con.close if con
end