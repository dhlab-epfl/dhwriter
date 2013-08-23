<xsl:stylesheet version="1.0"	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.tei-c.org/ns/1.0"
	xpath-default-namespace="http://www.w3.org/1999/xhtml">
	<xsl:output method="xml" encoding="utf-8" indent="yes"/>
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

			<profileDesc>
				<textClass>
					<keywords scheme="original" n="category">
						<term>Paper</term>
					</keywords>
					<keywords scheme="original" n="subcategory">
						<term>Long Paper</term>
					</keywords>
					<keywords scheme="original" n="keywords">
						<term>Image-based electronic editions</term>
						<term>Text-image linking</term>
					</keywords>
					<keywords scheme="original" n="topic">
						<term>image processing</term>
						<term>encoding &#8212; theory and practice</term>
						<term>digitisation, resource creation, and discovery</term>
						<term>scholarly editing</term>
						<term>digitisation &#8212; theory and practice</term>
						<term>linking and annotation</term>
					</keywords>
				</textClass>
			</profileDesc>

			<revisionDesc>
				<change>
					<date when="2013-04-01"></date>
					<name>Laura Weakly</name>
					<desc>Initial encoding</desc>
				</change>
			</revisionDesc>
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
			<body>
				<xsl:apply-templates/>
			</body>
		</text>
	</xsl:template>

	<xsl:template match="p">
		<p>
			<xsl:apply-templates select="*|@*|text()|comment()"/>
		</p>
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
		<graphic url="{$FOLDER}/{substring-after(@src,'/')}">
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

		</graphic>
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


	<!-- ignored HTML tags -->
	<xsl:template match="link"></xsl:template>
	<xsl:template match="meta"></xsl:template>

	</xsl:stylesheet>


