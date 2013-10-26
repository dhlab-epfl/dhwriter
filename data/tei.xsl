<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.tei-c.org/ns/1.0"
	xpath-default-namespace="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" encoding="utf-8" indent="yes"/>
	<xsl:strip-space elements="*"/>
	<xsl:param name="FOLDER"/>
	<xsl:param name="DATE_CREATED"/><!--20130719-->
	<xsl:param name="TIME_CREATED"/><!--10:30:00-->

	<xsl:template match="html">
		<TEI xmlns="http://www.tei-c.org/ns/1.0" xml:id="ab-112">
			<xsl:apply-templates/>
		</TEI>
	</xsl:template>

	<xsl:template match="head">
		<teiHeader>
			<fileDesc>
				<titleStmt>
					<title><xsl:value-of select="title"/></title>
					<xsl:apply-templates/>
				</titleStmt>
				<publicationStmt>
					<authority></authority>
					<publisher>EPFL, Switzerland</publisher>
					<distributor>
						<name>EPFL Digital Humanities Laboratory</name>
						<address>
							<addrLine>GC D2 386</addrLine>
							<addrLine>Station 18</addrLine>
							<addrLine>CH-1015 Lausanne</addrLine>
							<addrLine>frederic.kaplan@epfl.ch</addrLine>
						</address>
					</distributor>
					<pubPlace>Lausanne, Switzerland</pubPlace>
					<address>
						<addrLine>EPFL</addrLine>
						<addrLine>CH-1015 Lausanne</addrLine>
					</address>
					<availability>
						<p></p>
					</availability>
				</publicationStmt>

				<notesStmt><note type="abstract"><xsl:value-of select="meta[@name='description']/@content"/></note></notesStmt>

				<sourceDesc>
					<p>No source: created in electronic format.</p>
					<p>
						<date when="{$DATE_CREATED}"></date>
						<time when="{$TIME_CREATED}"></time>
					</p>
					<p n="session">LP22</p>
				</sourceDesc>
			</fileDesc>
		</teiHeader>
	</xsl:template>

	<xsl:template match="meta[@name='author']">
		<author>
			<xsl:variable name="fullname" select="substring-before(concat(@content,';'),';')"/>
			<xsl:variable name="details" select="substring-after(@content, ';')"/>
			<name>
				<surname><xsl:value-of select="substring-before(concat($fullname,','),',')"/></surname>
				<forename><xsl:value-of select="substring-after($fullname, ',')"/></forename>
			</name>
			<affiliation>
				<xsl:value-of select="substring-before(concat($details,';'),';')"/>
			</affiliation>
			<email>
				<xsl:value-of select="substring-after($details, ';')"/>
			</email>
		</author>
	</xsl:template>


	<xsl:template match="body">
		<text type="paper">
			<front>
				<head><xsl:apply-templates select="section[@id='header']/h1/*"/></head>
				<div>
					<xsl:for-each select="section[@id='header']/ul[@id='authors']/li">
						<p><xsl:value-of select="./*"/></p>
					</xsl:for-each>
				</div>
			</front>
			<body>
				<div>
					<xsl:apply-templates select="section[@id='article']"/>
				</div>
			</body>
			<back>
				<div type="References">
					<head><xsl:value-of select="section[@id='references']/h2"/></head>
					<xsl:for-each select="section[@id='references']/ol">
						<listBibl>
							<xsl:for-each select="li">
								<bibl>
									<hi rend="bold"><xsl:apply-templates/></hi>
								</bibl>
							</xsl:for-each>
						</listBibl>
					</xsl:for-each>
				</div>
			</back>
		</text>
	</xsl:template>

	<xsl:template match="h1|h2|h3|h4">
<!--	<head><xsl:apply-templates/></head>-->
		<p rend="head"><xsl:apply-templates/></p>
	</xsl:template>

	<xsl:template match="p">
		<xsl:if test=".!=''">
			<p><xsl:apply-templates select="*|@*|text()|comment()"/></p>
		</xsl:if>
	</xsl:template>

	<xsl:template match="p[@class='note']">
		<note>
			<xsl:apply-templates select="*|@*|text()|comment()"/>
		</note>
	</xsl:template>

	<xsl:template match="title">
	</xsl:template>

	<xsl:template match="ul">
		<list type="unordered">
			<xsl:apply-templates/>
		</list>
	</xsl:template>

	<xsl:template match="ol">
		<list type="ordered">
			<xsl:apply-templates/>
		</list>
	</xsl:template>

	<xsl:template match="li">
		<item>
			<xsl:apply-templates/>
		</item>
	</xsl:template>

	<xsl:template match="em">
		<hi rend="italic">
			<xsl:apply-templates/>
		</hi>
	</xsl:template>

	<xsl:template match="img">
		<figure>
			<graphic url="{$FOLDER}/{substring-after(@src,'/')}"></graphic>
			<xsl:for-each select="@width">
				<xsl:attribute name="width">
					<xsl:value-of select="."/>
				</xsl:attribute>
			</xsl:for-each>
			<xsl:for-each select="@height">
				<xsl:attribute name="height">
					<xsl:value-of select="."/>
				</xsl:attribute>
			</xsl:for-each>
		</figure>
	</xsl:template>

	<xsl:template match="pre">
		<eg>
			<xsl:apply-templates/>
		</eg>
	</xsl:template>

	<xsl:template match="strong">
		<hi rend="bold">
			<xsl:apply-templates/>
		</hi>
	</xsl:template>

	<xsl:template match="sup">
		<hi rend="sup">
			<xsl:apply-templates/>
		</hi>
	</xsl:template>

	<xsl:template match="semantics">
		<!-- formula (removed) -->
	</xsl:template>

	<!-- References -->
	<xsl:template match="cite">
		<!--<cite><xsl:value-of select="*" rend="small" /></cite>-->
	</xsl:template>

	<!-- ignored HTML tags -->
	<xsl:template match="link"></xsl:template>
	<xsl:template match="meta"></xsl:template>

</xsl:stylesheet>