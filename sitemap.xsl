<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html>
            <head>
                <title>Sitemap - Costa Resin & Construction</title>
                <style>
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        max-width: 1200px;
                        margin: 0 auto;
                        padding: 20px;
                        background: #f9fafb;
                    }
                    h1 {
                        color: #d97706;
                        border-bottom: 3px solid #d97706;
                        padding-bottom: 10px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        background: white;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }
                    th {
                        background: #d97706;
                        color: white;
                        padding: 12px 16px;
                        text-align: left;
                    }
                    td {
                        padding: 10px 16px;
                        border-bottom: 1px solid #e5e7eb;
                    }
                    tr:hover td {
                        background: #fef3c7;
                    }
                    a {
                        color: #d97706;
                        text-decoration: none;
                    }
                    a:hover {
                        text-decoration: underline;
                    }
                    .priority-high { color: #16a34a; font-weight: bold; }
                    .priority-medium { color: #d97706; }
                    .priority-low { color: #6b7280; }
                    .stats {
                        margin: 20px 0;
                        padding: 15px;
                        background: #fef3c7;
                        border-radius: 8px;
                        border-left: 4px solid #d97706;
                    }
                </style>
            </head>
            <body>
                <h1>🗺️ Sitemap - Costa Resin & Construction</h1>

                <div class="stats">
                    <strong>📊 Estadísticas del Sitemap</strong><br>
                    Total de URLs: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>URL</th>
                            <th>Última modificación</th>
                            <th>Frecuencia</th>
                            <th>Prioridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="sitemap:urlset/sitemap:url">
                            <tr>
                                <td><xsl:value-of select="position()"/></td>
                                <td>
                                    <a href="{sitemap:loc}">
                                        <xsl:value-of select="sitemap:loc"/>
                                    </a>
                                </td>
                                <td><xsl:value-of select="sitemap:lastmod"/></td>
                                <td><xsl:value-of select="sitemap:changefreq"/></td>
                                <td>
                                    <xsl:choose>
                                        <xsl:when test="sitemap:priority &gt;= 0.8">
                                            <span class="priority-high"><xsl:value-of select="sitemap:priority"/></span>
                                        </xsl:when>
                                        <xsl:when test="sitemap:priority &gt;= 0.5">
                                            <span class="priority-medium"><xsl:value-of select="sitemap:priority"/></span>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <span class="priority-low"><xsl:value-of select="sitemap:priority"/></span>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>

                <p style="margin-top: 20px; color: #6b7280; font-size: 14px;">
                    Generado por Costa Resin & Construction • 
                    <a href="https://TU_DOMINIO.com">Volver al sitio</a>
                </p>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
