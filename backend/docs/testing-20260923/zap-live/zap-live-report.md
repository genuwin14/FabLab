# FabLab OWASP ZAP - live site (passive)

ZAP by [Checkmarx](https://checkmarx.com/).


## Summary of Alerts

| Risk Level | Number of Alerts |
| --- | --- |
| High | 0 |
| Medium | 7 |
| Low | 6 |
| Informational | 4 |




## Insights

| Level | Reason | Site | Description | Statistic |
| --- | --- | --- | --- | --- |
| Low | Warning |  | ZAP errors logged - see the zap.log file for details | 1    |
| Low | Warning |  | ZAP warnings logged - see the zap.log file for details | 3    |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of responses with status code 2xx | 55 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of responses with status code 3xx | 38 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of responses with status code 4xx | 5 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with content type application/x-javascript | 5 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with content type image/png | 5 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with content type text/css | 5 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with content type text/html | 76 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with content type text/plain | 5 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of endpoints with method GET | 100 % |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Count of total endpoints | 17    |
| Info | Informational | https://palegoldenrod-kudu-488454.hostingersite.com | Percentage of slow responses | 100 % |




## Alerts

| Name | Risk Level | Number of Instances |
| --- | --- | --- |
| CSP: Failure to Define Directive with No Fallback | Medium | Systemic |
| CSP: Wildcard Directive | Medium | Systemic |
| CSP: script-src unsafe-inline | Medium | Systemic |
| CSP: style-src unsafe-inline | Medium | Systemic |
| Cross-Domain Misconfiguration | Medium | 1 |
| Missing Anti-clickjacking Header | Medium | 5 |
| Sub Resource Integrity Attribute Missing | Medium | Systemic |
| Big Redirect Detected (Potential Sensitive Information Leak) | Low | 7 |
| Cookie No HttpOnly Flag | Low | Systemic |
| Cross-Domain JavaScript Source File Inclusion | Low | Systemic |
| Server Leaks Information via "X-Powered-By" HTTP Response Header Field(s) | Low | Systemic |
| Strict-Transport-Security Header Not Set | Low | Systemic |
| X-Content-Type-Options Header Missing | Low | Systemic |
| Information Disclosure - Suspicious Comments | Informational | 11 |
| Modern Web Application | Informational | 2 |
| Re-examine Cache-control Directives | Informational | Systemic |
| Session Management Response Identified | Informational | 11 |




## Alert Detail



### [ CSP: Failure to Define Directive with No Fallback ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

The Content Security Policy fails to define one of the directives that has no fallback. Missing/excluding them is the same as allowing anything.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The directive(s): frame-ancestors, form-action is/are among the directives that do not fallback to default-src.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The directive(s): frame-ancestors, form-action is/are among the directives that do not fallback to default-src.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The directive(s): frame-ancestors, form-action is/are among the directives that do not fallback to default-src.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The directive(s): frame-ancestors, form-action is/are among the directives that do not fallback to default-src.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The directive(s): frame-ancestors, form-action is/are among the directives that do not fallback to default-src.`

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ CSP: Wildcard Directive ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The following directives either allow wildcard sources (or ancestors), are not defined, or are overly broadly defined:
script-src, style-src, img-src, connect-src, frame-src, font-src, media-src, object-src, manifest-src, worker-src`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The following directives either allow wildcard sources (or ancestors), are not defined, or are overly broadly defined:
script-src, style-src, img-src, connect-src, frame-src, font-src, media-src, object-src, manifest-src, worker-src`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The following directives either allow wildcard sources (or ancestors), are not defined, or are overly broadly defined:
script-src, style-src, img-src, connect-src, frame-src, font-src, media-src, object-src, manifest-src, worker-src`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The following directives either allow wildcard sources (or ancestors), are not defined, or are overly broadly defined:
script-src, style-src, img-src, connect-src, frame-src, font-src, media-src, object-src, manifest-src, worker-src`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `The following directives either allow wildcard sources (or ancestors), are not defined, or are overly broadly defined:
script-src, style-src, img-src, connect-src, frame-src, font-src, media-src, object-src, manifest-src, worker-src`

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ CSP: script-src unsafe-inline ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `script-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `script-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `script-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `script-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `script-src includes unsafe-inline.`

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ CSP: style-src unsafe-inline ](https://www.zaproxy.org/docs/alerts/10055/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks. Including (but not limited to) Cross Site Scripting (XSS), and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `style-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `style-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `style-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `style-src includes unsafe-inline.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: `content-security-policy`
  * Attack: ``
  * Evidence: `upgrade-insecure-requests`
  * Other Info: `style-src includes unsafe-inline.`

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is properly configured to set the Content-Security-Policy header.

### Reference


* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://caniuse.com/#search=content+security+policy ](https://caniuse.com/#search=content+security+policy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)
* [ https://github.com/HtmlUnit/htmlunit-csp ](https://github.com/HtmlUnit/htmlunit-csp)
* [ https://web.dev/articles/csp#resource-options ](https://web.dev/articles/csp#resource-options)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ Cross-Domain Misconfiguration ](https://www.zaproxy.org/docs/alerts/10098/)



##### Medium (Medium)

### Description

Web browser data loading may be possible, due to a Cross Origin Resource Sharing (CORS) misconfiguration on the web server.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/FABLAB-LOGO.png
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/FABLAB-LOGO.png`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Access-Control-Allow-Origin: *`
  * Other Info: `The CORS misconfiguration on the web server permits cross-domain read requests from arbitrary third party domains, using unauthenticated APIs on this domain. Web browser implementations do not permit arbitrary third parties to read the response from authenticated APIs, however. This reduces the risk somewhat. This misconfiguration could be used by an attacker to access data that is available in an unauthenticated manner, but which uses some other form of security, such as IP address white-listing.`


Instances: 1

### Solution

Ensure that sensitive data is not available in an unauthenticated manner (using IP address white-listing, for instance).
Configure the "Access-Control-Allow-Origin" HTTP header to a more restrictive set of domains, or remove all CORS headers entirely, to allow the web browser to enforce the Same Origin Policy (SOP) in a more restrictive manner.

### Reference


* [ https://vulncat.fortify.com/en/detail?category=HTML5&subcategory=Overly%20Permissive%20CORS%20Policy ](https://vulncat.fortify.com/en/detail?category=HTML5&subcategory=Overly%20Permissive%20CORS%20Policy)


#### CWE Id: [ 264 ](https://cwe.mitre.org/data/definitions/264.html)


#### WASC Id: 14

#### Source ID: 3

### [ Missing Anti-clickjacking Header ](https://www.zaproxy.org/docs/alerts/10020/)



##### Medium (Medium)

### Description

The response does not protect against 'ClickJacking' attacks. It should include either Content-Security-Policy with 'frame-ancestors' directive or X-Frame-Options.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``


Instances: 5

### Solution

Modern Web browsers support the Content-Security-Policy and X-Frame-Options HTTP headers. Ensure one of them is set on all web pages returned by your site/app.
If you expect the page to be framed only by pages on your server (e.g. it's part of a FRAMESET) then you'll want to use SAMEORIGIN, otherwise if you never expect the page to be framed, you should use DENY. Alternatively consider implementing Content Security Policy's "frame-ancestors" directive.

### Reference


* [ https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Frame-Options ](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Frame-Options)


#### CWE Id: [ 1021 ](https://cwe.mitre.org/data/definitions/1021.html)


#### WASC Id: 15

#### Source ID: 3

### [ Sub Resource Integrity Attribute Missing ](https://www.zaproxy.org/docs/alerts/90003/)



##### Medium (High)

### Description

The integrity attribute is missing on a script or link tag served by an external server. The integrity tag prevents an attacker who have gained access to this server from injecting a malicious content.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">`
  * Other Info: ``

Instances: Systemic


### Solution

Provide a valid integrity attribute to the tag.

### Reference


* [ https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity ](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity)


#### CWE Id: [ 345 ](https://cwe.mitre.org/data/definitions/345.html)


#### WASC Id: 15

#### Source ID: 3

### [ Big Redirect Detected (Potential Sensitive Information Leak) ](https://www.zaproxy.org/docs/alerts/10044/)



##### Low (Medium)

### Description

The server has responded with a redirect that seems to provide a large response. This may indicate that although the server sent a redirect it also responded with body content (which may include sensitive details, PII, etc.).

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/dashboard
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/dashboard`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/orders`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/users
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/users`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/customer/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/customer/orders`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/customer/shop
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/customer/shop`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/staff/dashboard
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/staff/dashboard`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/staff/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/staff/orders`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 57 [https://palegoldenrod-kudu-488454.hostingersite.com/login].
Predicted response size: 357.
Response Body Length: 474.`


Instances: 7

### Solution

Ensure that no sensitive information is leaked via redirect responses. Redirect responses should have almost no content.

### Reference



#### CWE Id: [ 201 ](https://cwe.mitre.org/data/definitions/201.html)


#### WASC Id: 13

#### Source ID: 3

### [ Cookie No HttpOnly Flag ](https://www.zaproxy.org/docs/alerts/10010/)



##### Low (Medium)

### Description

A cookie has been set without the HttpOnly flag, which means that the cookie can be accessed by JavaScript. If a malicious script can be run on this page then the cookie will be accessible and can be transmitted to another site. If this is a session cookie then session hijacking may be possible.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `set-cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``

Instances: Systemic


### Solution

Ensure that the HttpOnly flag is set for all cookies.

### Reference


* [ https://owasp.org/www-community/HttpOnly ](https://owasp.org/www-community/HttpOnly)


#### CWE Id: [ 1004 ](https://cwe.mitre.org/data/definitions/1004.html)


#### WASC Id: 13

#### Source ID: 3

### [ Cross-Domain JavaScript Source File Inclusion ](https://www.zaproxy.org/docs/alerts/10017/)



##### Low (Medium)

### Description

The page includes one or more script files from a third-party domain.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `https://code.jquery.com/jquery-3.7.1.min.js`
  * Attack: ``
  * Evidence: `<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `https://code.jquery.com/jquery-3.7.1.min.js`
  * Attack: ``
  * Evidence: `<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>`
  * Other Info: ``

Instances: Systemic


### Solution

Ensure JavaScript source files are loaded from only trusted sources, and the sources can't be controlled by end users of the application.

### Reference



#### CWE Id: [ 829 ](https://cwe.mitre.org/data/definitions/829.html)


#### WASC Id: 15

#### Source ID: 3

### [ Server Leaks Information via "X-Powered-By" HTTP Response Header Field(s) ](https://www.zaproxy.org/docs/alerts/10037/)



##### Low (Medium)

### Description

The web/application server is leaking information via one or more "X-Powered-By" HTTP response headers. Access to such information may facilitate attackers identifying other frameworks/components your web application is reliant upon and the vulnerabilities such components may be subject to.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.3.33`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `x-powered-by: PHP/8.3.33`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `x-powered-by: PHP/8.3.33`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `x-powered-by: PHP/8.3.33`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `x-powered-by: PHP/8.3.33`
  * Other Info: ``

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is configured to suppress "X-Powered-By" headers.

### Reference


* [ https://owasp.org/www-project-web-security-testing-guide/v42/4-Web_Application_Security_Testing/01-Information_Gathering/08-Fingerprint_Web_Application_Framework ](https://owasp.org/www-project-web-security-testing-guide/v42/4-Web_Application_Security_Testing/01-Information_Gathering/08-Fingerprint_Web_Application_Framework)
* [ https://www.troyhunt.com/shhh-dont-let-your-response-headers/ ](https://www.troyhunt.com/shhh-dont-let-your-response-headers/)


#### CWE Id: [ 497 ](https://cwe.mitre.org/data/definitions/497.html)


#### WASC Id: 13

#### Source ID: 3

### [ Strict-Transport-Security Header Not Set ](https://www.zaproxy.org/docs/alerts/10035/)



##### Low (High)

### Description

HTTP Strict Transport Security (HSTS) is a web security policy mechanism whereby a web server declares that complying user agents (such as a web browser) are to interact with it using only secure HTTPS connections (i.e. HTTP layered over TLS/SSL). HSTS is an IETF standards track protocol and is specified in RFC 6797.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/sitemap.xml`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is configured to enforce Strict-Transport-Security.

### Reference


* [ https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Strict_Transport_Security_Cheat_Sheet.html ](https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Strict_Transport_Security_Cheat_Sheet.html)
* [ https://owasp.org/www-community/Security_Headers ](https://owasp.org/www-community/Security_Headers)
* [ https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security ](https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security)
* [ https://caniuse.com/stricttransportsecurity ](https://caniuse.com/stricttransportsecurity)
* [ https://datatracker.ietf.org/doc/html/rfc6797 ](https://datatracker.ietf.org/doc/html/rfc6797)


#### CWE Id: [ 319 ](https://cwe.mitre.org/data/definitions/319.html)


#### WASC Id: 15

#### Source ID: 3

### [ X-Content-Type-Options Header Missing ](https://www.zaproxy.org/docs/alerts/10021/)



##### Low (Medium)

### Description

The Anti-MIME-Sniffing header X-Content-Type-Options was not set to 'nosniff'. This allows older versions of Internet Explorer and Chrome to perform MIME-sniffing on the response body, potentially causing the response body to be interpreted and displayed as a content type other than the declared content type. Current (early 2014) and legacy versions of Firefox will use the declared content type (if one is set), rather than performing MIME-sniffing.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-COV1aA00.css
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-COV1aA00.css`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`

Instances: Systemic


### Solution

Ensure that the application/web server sets the Content-Type header appropriately, and that it sets the X-Content-Type-Options header to 'nosniff' for all web pages.
If possible, ensure that the end user uses a standards-compliant and modern web browser that does not perform MIME-sniffing at all, or that can be directed by the web application/web server to not perform MIME-sniffing.

### Reference


* [ https://learn.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/gg622941(v=vs.85) ](https://learn.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/compatibility/gg622941(v=vs.85))
* [ https://owasp.org/www-community/Security_Headers ](https://owasp.org/www-community/Security_Headers)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ Information Disclosure - Suspicious Comments ](https://www.zaproxy.org/docs/alerts/10027/)



##### Informational (Low)

### Description

The response appears to contain suspicious comments which may help an attacker.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/build/assets/app-Kijzqt9A.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `user`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "//localhost",un=Object.freeze(Object.defineProperty({__proto__:null,hasBrowserEnv:Ee,hasStandardBrowserEnv:an,hasStandardBrowser", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "<!-- ==================================================================
         Notification toasts (admin / staff / customer)
", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "<!-- ==================================================================
         Notification toasts (admin / staff / customer)
", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "<!-- ==================================================================
         Notification toasts (admin / staff / customer)
", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "<!-- ==================================================================
         Notification toasts (admin / staff / customer)
", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "<!-- ==================================================================
         Notification toasts (admin / staff / customer)
", see evidence field for the suspicious comment/snippet.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`


Instances: 11

### Solution

Remove all comments that return information that may help an attacker and fix any underlying problems they refer to.

### Reference



#### CWE Id: [ 615 ](https://cwe.mitre.org/data/definitions/615.html)


#### WASC Id: 13

#### Source ID: 3

### [ Modern Web Application ](https://www.zaproxy.org/docs/alerts/10109/)



##### Informational (Medium)

### Description

The application appears to be a modern web application. If you need to explore it automatically then the Ajax Spider may well be more effective than the standard one.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`


Instances: 2

### Solution

This is an informational alert and so no changes are required.

### Reference




#### Source ID: 3

### [ Re-examine Cache-control Directives ](https://www.zaproxy.org/docs/alerts/10015/)



##### Informational (Low)

### Description

The cache-control header has not been set properly or is missing, allowing the browser and proxies to cache content. For static assets like css, js, or image files this might be intended, however, the resources should be reviewed to ensure that no sensitive content will be cached.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `cache-control`
  * Attack: ``
  * Evidence: `no-cache, private`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `cache-control`
  * Attack: ``
  * Evidence: `no-cache, private`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `cache-control`
  * Attack: ``
  * Evidence: `no-cache, private`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/register
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/register`
  * Method: `GET`
  * Parameter: `cache-control`
  * Attack: ``
  * Evidence: `no-cache, private`
  * Other Info: ``
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/robots.txt`
  * Method: `GET`
  * Parameter: `cache-control`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``

Instances: Systemic


### Solution

For secure content, ensure the cache-control HTTP header is set with "no-cache, no-store, must-revalidate". If an asset should be cached consider setting the directives "public, max-age, immutable".

### Reference


* [ https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html#web-content-caching ](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html#web-content-caching)
* [ https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Cache-Control ](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Cache-Control)
* [ https://grayduck.mn/2021/09/13/cache-control-recommendations/ ](https://grayduck.mn/2021/09/13/cache-control-recommendations/)


#### CWE Id: [ 525 ](https://cwe.mitre.org/data/definitions/525.html)


#### WASC Id: 13

#### Source ID: 3

### [ Session Management Response Identified ](https://www.zaproxy.org/docs/alerts/10112/)



##### Informational (Medium)

### Description

The given response has been identified as containing a session management token. The 'Other Info' field contains a set of header tokens that can be used in the Header Based Session Management Method. If the request is in a context which has a Session Management Method set to "Auto-Detect" then this rule will change the session management to use the tokens identified.

* URL: https://palegoldenrod-kudu-488454.hostingersite.com
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/dashboard
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/dashboard`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/orders`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/admin/users
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/admin/users`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/customer/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/customer/orders`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/customer/shop
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/customer/shop`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/forgot-password`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/login
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/login`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/staff/dashboard
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/staff/dashboard`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`
* URL: https://palegoldenrod-kudu-488454.hostingersite.com/staff/orders
  * Node Name: `https://palegoldenrod-kudu-488454.hostingersite.com/staff/orders`
  * Method: `GET`
  * Parameter: `fablab-session`
  * Attack: ``
  * Evidence: `fablab-session`
  * Other Info: `cookie:fablab-session
cookie:XSRF-TOKEN`


Instances: 11

### Solution

This is an informational alert rather than a vulnerability and so there is nothing to fix.

### Reference


* [ https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/ ](https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/)



#### Source ID: 3


