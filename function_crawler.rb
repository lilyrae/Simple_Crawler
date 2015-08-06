def get_article(article, con)

	title = article.css('h2').text.strip!
	date = article.css('h3').text.strip!
	tease = article.css('div[class="no-object"]').text.strip!
	source = 'radiolab'

	# check if there is an author for the article
	if article.css('p[class="byline"]').any?
		author = article.css('p[class="byline"]').css('a').text
	else
		author = 'unknown'
	end
			
	# replace ' with '' in fields to stop problems with sql query
	title.gsub! "'", "''"
	tease.gsub! "'", "''"
			
	# remove special character &nbsp; from fields
	title.gsub! "\u00A0", " "
	tease.gsub! "\u00A0", " "
	author.gsub! "\u00A0", " "
			
	# update sql database
	query = con.query "INSERT INTO  `simple_crawler`.`Article_Summary` (`ID`,`Title`,`Article_Date`,`Author`,`Tease`,`Source`,`Updated`)
					VALUES (NULL,'#{title}','#{date}','#{author}','#{tease}','#{source}',CURRENT_TIMESTAMP)
					ON DUPLICATE KEY UPDATE Tease='#{tease}', Updated=CURRENT_TIMESTAMP"

	return title

end

def get_content(article, title, con)

	article_url = article.css('h2').css('a').map { |link| link['href'] }
	content_page = Nokogiri::HTML(open(article_url[0]))

	content = content_page.css('div[class="article-description"]')

	content = content.to_html
	content.gsub! "\u00A0", " "
	content.gsub! '"', '\"'
	content.gsub! "'", "\'"

	references = content_page.css('ul[class="storylinks"]').css('a').map { |link| link['href'] }.first
	puts title

	article_ID = (con.query "SELECT `ID` FROM `simple_crawler`.`Article_Summary` WHERE `Title`='#{title}'").fetch_row.first
	puts article_ID

	# update sql database
	query = con.query "INSERT INTO  `simple_crawler`.`Article` (`ID`,`Summary_ID`,`Content`,`References`)
					VALUES (NULL,'#{article_ID}',\"#{content}\",'#{references}')
					ON DUPLICATE KEY UPDATE Content=\"#{content}\", Article.References='#{references}'"

end
