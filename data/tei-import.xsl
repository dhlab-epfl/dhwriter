<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:tei="http://www.tei-c.org/ns/1.0">

  <xsl:output method="xml" encoding="utf-8" indent="yes"/>

  <xsl:variable name="ns-xhtml"
    select="'http://www.w3.org/1999/xhtml'"/>

  <xsl:strip-space elements="*"/>

  <xsl:template match="tei:TEI">
    <xsl:element name="html" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:teiHeader/tei:fileDesc">
    <xsl:element name="head" namespace="{$ns-xhtml}">
      <xsl:element name="title" namespace="{$ns-xhtml}">
        <xsl:value-of select="tei:titleStmt/tei:title[1]"/>
      </xsl:element>
      <xsl:element name="meta" namespace="{$ns-xhtml}">
        <xsl:attribute name="name">description</xsl:attribute>
        <xsl:attribute name="content">
          <xsl:value-of select="normalize-space(tei:notesStmt/tei:note[@type='abstract'])"
          />
        </xsl:attribute>
      </xsl:element>
      <xsl:for-each select="tei:titleStmt/tei:author">
        <xsl:element name="meta" namespace="{$ns-xhtml}">
          <xsl:attribute name="name">author</xsl:attribute>
          <xsl:attribute name="content">
            <xsl:value-of
              select="concat(tei:name/tei:surname,
			      ',',
			      tei:name/tei:forename,
			      ';',
			      string(tei:affiliation),
			      ';',
			      string(tei:email))"
            />
          </xsl:attribute>
        </xsl:element>
      </xsl:for-each>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:text">
    <xsl:element name="body" namespace="{$ns-xhtml}">

      <xsl:element name="section" namespace="{$ns-xhtml}">
        <xsl:attribute name="id">header</xsl:attribute>

        <xsl:element name="h1" namespace="{$ns-xhtml}">
          <xsl:apply-templates
            select="/tei:TEI/tei:teiHeader/tei:fileDesc
				       /tei:titleStmt/tei:title"
          />
        </xsl:element>
        <xsl:element name="ul" namespace="{$ns-xhtml}">
          <xsl:attribute name="id">authors</xsl:attribute>
          <xsl:for-each
            select="/tei:TEI/tei:teiHeader/tei:fileDesc
				/tei:titleStmt/tei:author">
            <xsl:element name="li" namespace="{$ns-xhtml}">
              <xsl:element name="span" namespace="{$ns-xhtml}">
                <xsl:attribute name="class"
                  >author-surname</xsl:attribute>
                <xsl:value-of select="tei:name/tei:surname"/>
              </xsl:element>
              <xsl:element name="span" namespace="{$ns-xhtml}">
                <xsl:attribute name="class"
                  >author-forename</xsl:attribute>
                <xsl:value-of select="tei:name/tei:forename"/>
              </xsl:element>
              <xsl:element name="span" namespace="{$ns-xhtml}">
                <xsl:attribute name="class"
                  >author-affiliation</xsl:attribute>
                <xsl:value-of select="tei:affiliation"/>
              </xsl:element>
              <xsl:element name="span" namespace="{$ns-xhtml}">
                <xsl:attribute name="class"
                  >author-email</xsl:attribute>
                <xsl:value-of select="tei:email"/>
              </xsl:element>
            </xsl:element>
          </xsl:for-each>
        </xsl:element>
      </xsl:element>

      <xsl:element name="section" namespace="{$ns-xhtml}">
        <xsl:attribute name="id">article</xsl:attribute>
        <xsl:apply-templates select="tei:body"/>
      </xsl:element>

      <xsl:element name="section" namespace="{$ns-xhtml}">
        <xsl:attribute name="id">references</xsl:attribute>
        <xsl:apply-templates select="tei:back"/>
      </xsl:element>

    </xsl:element>

  </xsl:template>

  <xsl:template match="tei:body">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template
    match="tei:div | tei:div1 | tei:div2 | tei:div3 | tei:div4">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="tei:body/tei:div/tei:head | tei:div1/tei:head">
    <xsl:element name="h1" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template
    match="tei:body/tei:div/tei:div/tei:head 
		       | tei:div2/tei:head">
    <xsl:element name="h2" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template
    match="tei:body/tei:div/tei:div/tei:div/tei:head 
		       | tei:div3/tei:head">
    <xsl:element name="h3" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template
    match="tei:body/tei:div/tei:div/tei:div/tei:div/tei:head 
		       | tei:div4/tei:head">
    <xsl:element name="h4" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:p">
    <xsl:element name="p" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:note">
    <xsl:element name="p" namespace="{$ns-xhtml}">
      <xsl:attribute name="class">note</xsl:attribute>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:list">
    <xsl:element name="ul" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:list[@type='ordered']">
    <xsl:element name="ol" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:list[@type='unordered' or @type='bullets']">
    <xsl:element name="ul" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:item">
    <xsl:element name="li" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:emph | tei:hi | tei:hi[@rend='italic']">
    <xsl:element name="em" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:hi[@rend='bold']">
    <xsl:element name="strong" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:hi[@rend='sup']">
    <xsl:element name="sup" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:hi[@rend='sub']">
    <xsl:element name="sub" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:figure[tei:graphic]">
    <xsl:element name="img" namespace="{$ns-xhtml}">
      <xsl:attribute name="src">
        <xsl:value-of select="tei:graphic/@url"/>
      </xsl:attribute>
      <xsl:apply-templates select="@height | @width"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="@height | @width">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="tei:eg">
    <xsl:element name="pre" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <!-- References -->
  <xsl:template match="ref">
    <xsl:variable name="fragid" select="substring-after(@target,'#')"/>
    <xsl:choose>
      <xsl:when
        test="@type='bibref' 
		      or 
		      (starts-with(@target,'#')
		      and
		      //*[@xml:id = $fragid]/self::tei:bibl)">
        <xsl:element name="a" namespace="{$ns-xhtml}">
          <xsl:attribute name="href">
            <xsl:value-of select="@target"/>
          </xsl:attribute>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="a" namespace="{$ns-xhtml}">
          <xsl:attribute name="href">
            <xsl:value-of select="@target"/>
          </xsl:attribute>
          <xsl:apply-templates/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>


  <xsl:template match="tei:back">
    <xsl:message>hi, mom</xsl:message>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="tei:back/tei:div[not(@type='References')]"/> 
  
  <xsl:template match="tei:back/tei:div[@type='References']">
    <xsl:message>hi, bro</xsl:message>
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="tei:back/tei:div[@type='References']/tei:head"/>
  
  <xsl:template
    match="tei:back/tei:div[@type='References']/tei:listBibl">
    <xsl:message>hi, dad</xsl:message>
    <xsl:element name="ol" namespace="{$ns-xhtml}">
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="tei:back//tei:listBibl/tei:bibl">
    <xsl:element name="li" namespace="{$ns-xhtml}">
      <!--* At the moment, Aloha Editor doesn't seem to be set up
          * for internal markup in the citations.
	  * That may change someday.
	  *-->
      <xsl:value-of select="string(.)"/>
    </xsl:element>
  </xsl:template>

</xsl:stylesheet>