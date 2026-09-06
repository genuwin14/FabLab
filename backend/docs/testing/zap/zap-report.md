# FabLab OWASP ZAP scan

ZAP by [Checkmarx](https://checkmarx.com/).


## Summary of Alerts

| Risk Level | Number of Alerts |
| --- | --- |
| High | 0 |
| Medium | 3 |
| Low | 7 |
| Informational | 6 |




## Insights

| Level | Reason | Site | Description | Statistic |
| --- | --- | --- | --- | --- |
| Low | Warning |  | ZAP errors logged - see the zap.log file for details | 2    |
| Low | Warning |  | ZAP warnings logged - see the zap.log file for details | 36    |
| Low | Exceeded High | http://127.0.0.1:8080 | Percentage of slow responses | 99 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of responses with status code 2xx | 61 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of responses with status code 3xx | 28 % |
| Info | Exceeded Low | http://127.0.0.1:8080 | Percentage of responses with status code 4xx | 10 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with content type application/pdf | 7 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with content type image/png | 2 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with content type text/html | 76 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with content type text/javascript | 12 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with content type text/plain | 1 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with method GET | 88 % |
| Info | Informational | http://127.0.0.1:8080 | Percentage of endpoints with method POST | 11 % |
| Info | Informational | http://127.0.0.1:8080 | Count of total endpoints | 94    |




## Alerts

| Name | Risk Level | Number of Instances |
| --- | --- | --- |
| Content Security Policy (CSP) Header Not Set | Medium | Systemic |
| Missing Anti-clickjacking Header | Medium | Systemic |
| Sub Resource Integrity Attribute Missing | Medium | Systemic |
| Big Redirect Detected (Potential Sensitive Information Leak) | Low | 9 |
| Cookie No HttpOnly Flag | Low | Systemic |
| Cross-Domain JavaScript Source File Inclusion | Low | Systemic |
| In Page Banner Information Leak | Low | Systemic |
| Server Leaks Information via "X-Powered-By" HTTP Response Header Field(s) | Low | Systemic |
| Server Leaks Version Information via "Server" HTTP Response Header Field | Low | Systemic |
| X-Content-Type-Options Header Missing | Low | Systemic |
| Authentication Request Identified | Informational | 1 |
| Information Disclosure - Suspicious Comments | Informational | 47 |
| Modern Web Application | Informational | Systemic |
| Session Management Response Identified | Informational | 40 |
| User Agent Fuzzer | Informational | Systemic |
| User Controllable HTML Element Attribute (Potential XSS) | Informational | 3 |




## Alert Detail



### [ Content Security Policy (CSP) Header Not Set ](https://www.zaproxy.org/docs/alerts/10038/)



##### Medium (High)

### Description

Content Security Policy (CSP) is an added layer of security that helps to detect and mitigate certain types of attacks, including Cross Site Scripting (XSS) and data injection attacks. These attacks are used for everything from data theft to site defacement or distribution of malware. CSP provides a set of standard HTTP headers that allow website owners to declare approved sources of content that browsers should be allowed to load on that page — covered types are JavaScript, CSS, HTML frames, fonts, images and embeddable objects such as Java applets, ActiveX, audio and video files.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/sitemap.xml
  * Node Name: `http://127.0.0.1:8080/sitemap.xml`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: ``

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is configured to set the Content-Security-Policy header.

### Reference


* [ https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP ](https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/CSP)
* [ https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html ](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html)
* [ https://www.w3.org/TR/CSP/ ](https://www.w3.org/TR/CSP/)
* [ https://w3c.github.io/webappsec-csp/ ](https://w3c.github.io/webappsec-csp/)
* [ https://web.dev/articles/csp ](https://web.dev/articles/csp)
* [ https://caniuse.com/#feat=contentsecuritypolicy ](https://caniuse.com/#feat=contentsecuritypolicy)
* [ https://content-security-policy.com/ ](https://content-security-policy.com/)


#### CWE Id: [ 693 ](https://cwe.mitre.org/data/definitions/693.html)


#### WASC Id: 15

#### Source ID: 3

### [ Missing Anti-clickjacking Header ](https://www.zaproxy.org/docs/alerts/10020/)



##### Medium (Medium)

### Description

The response does not protect against 'ClickJacking' attacks. It should include either Content-Security-Policy with 'frame-ancestors' directive or X-Frame-Options.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register`
  * Method: `GET`
  * Parameter: `x-frame-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: ``

Instances: Systemic


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

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">`
  * Other Info: ``
* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">`
  * Other Info: ``
* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">`
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

* URL: http://127.0.0.1:8080/customer/cart/checkout
  * Node Name: `http://127.0.0.1:8080/customer/cart/checkout ()(_token)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 35 [http://127.0.0.1:8080/customer/cart].
Predicted response size: 335.
Response Body Length: 386.`
* URL: http://127.0.0.1:8080/customer/profile
  * Node Name: `http://127.0.0.1:8080/customer/profile ()(_method,_token,address,contact_number,degree,email,fullname,gender,photo,section,year)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 35 [http://127.0.0.1:8080/customer/shop].
Predicted response size: 335.
Response Body Length: 386.`
* URL: http://127.0.0.1:8080/customer/settings
  * Node Name: `http://127.0.0.1:8080/customer/settings ()(_method,_token,notifications_enabled)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 39 [http://127.0.0.1:8080/customer/settings].
Predicted response size: 339.
Response Body Length: 402.`
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password ()(_token,email)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 37 [http://127.0.0.1:8080/forgot-password].
Predicted response size: 337.
Response Body Length: 394.`
* URL: http://127.0.0.1:8080/forgot-password/send
  * Node Name: `http://127.0.0.1:8080/forgot-password/send ()(_token,email,verification_mode)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 37 [http://127.0.0.1:8080/forgot-password].
Predicted response size: 337.
Response Body Length: 394.`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login ()(_token,email,password)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 27 [http://127.0.0.1:8080/login].
Predicted response size: 327.
Response Body Length: 354.`
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register ()(_token,email,name,password,password_confirmation,phone)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 30 [http://127.0.0.1:8080/register].
Predicted response size: 330.
Response Body Length: 366.`
* URL: http://127.0.0.1:8080/verify-code
  * Node Name: `http://127.0.0.1:8080/verify-code ()(_token,otp,verification_mode)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 33 [http://127.0.0.1:8080/verify-code].
Predicted response size: 333.
Response Body Length: 378.`
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend ()(_token,verification_mode)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: ``
  * Other Info: `Location header URI length: 27 [http://127.0.0.1:8080/login].
Predicted response size: 327.
Response Body Length: 354.`


Instances: 9

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

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register ()(_token,email,name,password,password_confirmation,phone)`
  * Method: `POST`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `Set-Cookie: XSRF-TOKEN`
  * Other Info: ``
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend ()(_token,verification_mode)`
  * Method: `POST`
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

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `https://code.jquery.com/jquery-3.7.1.min.js`
  * Attack: ``
  * Evidence: `<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>`
  * Other Info: ``
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js`
  * Attack: ``
  * Evidence: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
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

### [ In Page Banner Information Leak ](https://www.zaproxy.org/docs/alerts/10009/)



##### Low (High)

### Description

The server returned a version banner string in the response content. Such information leaks may allow attackers to further target specific issues impacting the product and version in use.

* URL: http://127.0.0.1:8080/'%2520+%2520(it.url%2520%257C%257C%2520INDEX_URL&29%2520+%2520'
  * Node Name: `http://127.0.0.1:8080/'   (it.url || INDEX_URL)   '`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58`
  * Other Info: `There is a chance that the highlight in the finding is on a value in the headers, versus the actual matched string in the response body.`
* URL: http://127.0.0.1:8080/%255C
  * Node Name: `http://127.0.0.1:8080/\`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58`
  * Other Info: `There is a chance that the highlight in the finding is on a value in the headers, versus the actual matched string in the response body.`
* URL: http://127.0.0.1:8080/%255Cs/
  * Node Name: `http://127.0.0.1:8080/\s/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58`
  * Other Info: `There is a chance that the highlight in the finding is on a value in the headers, versus the actual matched string in the response body.`
* URL: http://127.0.0.1:8080/%255E%255C
  * Node Name: `http://127.0.0.1:8080/^\`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58`
  * Other Info: `There is a chance that the highlight in the finding is on a value in the headers, versus the actual matched string in the response body.`
* URL: http://127.0.0.1:8080/%255E%255C%255Ck
  * Node Name: `http://127.0.0.1:8080/^\\k`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58`
  * Other Info: `There is a chance that the highlight in the finding is on a value in the headers, versus the actual matched string in the response body.`

Instances: Systemic


### Solution

Configure the server to prevent such information leaks. For example:
Under Tomcat this is done via the "server" directive and implementation of custom error pages.
Under Apache this is done via the "ServerSignature" and "ServerTokens" directives.

### Reference


* [ https://owasp.org/www-project-web-security-testing-guide/v41/4-Web_Application_Security_Testing/08-Testing_for_Error_Handling/ ](https://owasp.org/www-project-web-security-testing-guide/v41/4-Web_Application_Security_Testing/08-Testing_for_Error_Handling/)


#### CWE Id: [ 497 ](https://cwe.mitre.org/data/definitions/497.html)


#### WASC Id: 13

#### Source ID: 3

### [ Server Leaks Information via "X-Powered-By" HTTP Response Header Field(s) ](https://www.zaproxy.org/docs/alerts/10037/)



##### Low (Medium)

### Description

The web/application server is leaking information via one or more "X-Powered-By" HTTP response headers. Access to such information may facilitate attackers identifying other frameworks/components your web application is reliant upon and the vulnerabilities such components may be subject to.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/sitemap.xml
  * Node Name: `http://127.0.0.1:8080/sitemap.xml`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend ()(_token,verification_mode)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `X-Powered-By: PHP/8.2.12`
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

### [ Server Leaks Version Information via "Server" HTTP Response Header Field ](https://www.zaproxy.org/docs/alerts/10036/)



##### Low (High)

### Description

The web/application server is leaking version information via the "Server" HTTP response header. Access to such information may facilitate attackers identifying other vulnerabilities your web/application server is subject to.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/FABLAB-LOGO.png
  * Node Name: `http://127.0.0.1:8080/FABLAB-LOGO.png`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/robots.txt
  * Node Name: `http://127.0.0.1:8080/robots.txt`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  * Other Info: ``
* URL: http://127.0.0.1:8080/sitemap.xml
  * Node Name: `http://127.0.0.1:8080/sitemap.xml`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Apache/2.4.58 (Win64) OpenSSL/3.1.3 PHP/8.2.12`
  * Other Info: ``

Instances: Systemic


### Solution

Ensure that your web server, application server, load balancer, etc. is configured to suppress the "Server" header or provide generic details.

### Reference


* [ https://httpd.apache.org/docs/current/mod/core.html#servertokens ](https://httpd.apache.org/docs/current/mod/core.html#servertokens)
* [ https://learn.microsoft.com/en-us/previous-versions/msp-n-p/ff648552(v=pandp.10) ](https://learn.microsoft.com/en-us/previous-versions/msp-n-p/ff648552(v=pandp.10))
* [ https://www.troyhunt.com/shhh-dont-let-your-response-headers/ ](https://www.troyhunt.com/shhh-dont-let-your-response-headers/)


#### CWE Id: [ 497 ](https://cwe.mitre.org/data/definitions/497.html)


#### WASC Id: 13

#### Source ID: 3

### [ X-Content-Type-Options Header Missing ](https://www.zaproxy.org/docs/alerts/10021/)



##### Low (Medium)

### Description

The Anti-MIME-Sniffing header X-Content-Type-Options was not set to 'nosniff'. This allows older versions of Internet Explorer and Chrome to perform MIME-sniffing on the response body, potentially causing the response body to be interpreted and displayed as a content type other than the declared content type. Current (early 2014) and legacy versions of Firefox will use the declared content type (if one is set), rather than performing MIME-sniffing.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://127.0.0.1:8080/FABLAB-LOGO.png
  * Node Name: `http://127.0.0.1:8080/FABLAB-LOGO.png`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `x-content-type-options`
  * Attack: ``
  * Evidence: ``
  * Other Info: `This issue still applies to error type pages (401, 403, 500, etc.) as those pages are often still affected by injection issues, in which case there is still concern for browsers sniffing pages away from their actual content type.
At "High" threshold this scan rule will not alert on client or server error responses.`
* URL: http://127.0.0.1:8080/robots.txt
  * Node Name: `http://127.0.0.1:8080/robots.txt`
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

### [ Authentication Request Identified ](https://www.zaproxy.org/docs/alerts/10111/)



##### Informational (High)

### Description

The given request has been identified as an authentication request. The 'Other Info' field contains a set of key=value lines which identify any relevant fields. If the request is in a context which has an Authentication Method set to "Auto-Detect" then this rule will change the authentication to match the request identified.

* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login ()(_token,email,password)`
  * Method: `POST`
  * Parameter: `email`
  * Attack: ``
  * Evidence: `password`
  * Other Info: `userParam=email
userValue=zaproxy@example.com
passwordParam=password
referer=http://127.0.0.1:8080/login
csrfToken=_token`


Instances: 1

### Solution

This is an informational alert rather than a vulnerability and so there is nothing to fix.

### Reference


* [ https://www.zaproxy.org/docs/desktop/addons/authentication-helper/auth-req-id/ ](https://www.zaproxy.org/docs/desktop/addons/authentication-helper/auth-req-id/)



#### Source ID: 3

### [ Information Disclosure - Suspicious Comments ](https://www.zaproxy.org/docs/alerts/10027/)



##### Informational (Medium)

### Description

The response appears to contain suspicious comments which may help an attacker.

* URL: http://127.0.0.1:8080/customer/cart
  * Node Name: `http://127.0.0.1:8080/customer/cart`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Select`
  * Other Info: `The following pattern was used: \bSELECT\b and was detected in likely comment: "// Select All Toggle", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize
  * Node Name: `http://127.0.0.1:8080/customer/customize`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "// Admin → Customization Pricing. The live quote must use these and", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize%3Fproduct_id=2
  * Node Name: `http://127.0.0.1:8080/customer/customize (product_id)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "// Admin → Customization Pricing. The live quote must use these and", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/forgot-password/send
  * Node Name: `http://127.0.0.1:8080/forgot-password/send`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `select`
  * Other Info: `The following pattern was used: \bSELECT\b and was detected in likely comment: "//alpinejs.dev/plugins/${n}`,a))}W("modelable",(e,{expression:t},{effect:n,evaluateLater:a,cleanup:r})=>{let i=a(t),s=()=>{let u", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/core.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/core.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Initial model based on product type from config", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/handlers.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/handlers.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Initialize the finish from whichever swatch is active (rendered server-side).", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/logic.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/logic.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Admin`
  * Other Info: `The following pattern was used: \bADMIN\b and was detected in likely comment: "/**
 * Customization rates for the live quote.
 *
 * The amounts come from Admin → Customization Pricing via CustomizerConfig.ra", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/bag.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/bag.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/mug.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/mug.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/polo.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/polo.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/shorts.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/shorts.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/t-shirt.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/t-shirt.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/models/umbrella.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/models/umbrella.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// Clear existing children from model_group", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/persistence.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/persistence.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "// 2. Directly populate the element arrays from the recipe (bypass DOM round-trip)", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/rendering.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/rendering.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected in likely comment: "/**
 * Look up a texture entry from CustomizerConfig.textures by id.
 * Returns null if not found.
 */", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/js/customizer/state.js
  * Node Name: `http://127.0.0.1:8080/js/customizer/state.js`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `Where`
  * Other Info: `The following pattern was used: \bWHERE\b and was detected in likely comment: "/**
 * Where a model's printable panels sit on the design canvas, in UV space.
 *
 * Elements are placed relative to their own p", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `select`
  * Other Info: `The following pattern was used: \bSELECT\b and was detected in likely comment: "//alpinejs.dev/plugins/${n}`,a))}W("modelable",(e,{expression:t},{effect:n,evaluateLater:a,cleanup:r})=>{let i=a(t),s=()=>{let u", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/orders
  * Node Name: `http://127.0.0.1:8080/customer/orders ()(_token)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `select`
  * Other Info: `The following pattern was used: \bSELECT\b and was detected in likely comment: "//alpinejs.dev/plugins/${n}`,a))}W("modelable",(e,{expression:t},{effect:n,evaluateLater:a,cleanup:r})=>{let i=a(t),s=()=>{let u", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/orders
  * Node Name: `http://127.0.0.1:8080/customer/orders ()(_token,pr_number)`
  * Method: `POST`
  * Parameter: ``
  * Attack: ``
  * Evidence: `select`
  * Other Info: `The following pattern was used: \bSELECT\b and was detected in likely comment: "//alpinejs.dev/plugins/${n}`,a))}W("modelable",(e,{expression:t},{effect:n,evaluateLater:a,cleanup:r})=>{let i=a(t),s=()=>{let u", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/cart
  * Node Name: `http://127.0.0.1:8080/customer/cart`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/cart
  * Node Name: `http://127.0.0.1:8080/customer/cart`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize
  * Node Name: `http://127.0.0.1:8080/customer/customize`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize
  * Node Name: `http://127.0.0.1:8080/customer/customize`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 4 times, the first in likely comment: "<!-- Design panel picker — populated from the loaded model's zones, and
             hidden entirely for models that only have o", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize%3Fproduct_id=2
  * Node Name: `http://127.0.0.1:8080/customer/customize (product_id)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/customize%3Fproduct_id=2
  * Node Name: `http://127.0.0.1:8080/customer/customize (product_id)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 4 times, the first in likely comment: "<!-- Design panel picker — populated from the loaded model's zones, and
             hidden entirely for models that only have o", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/my-designs
  * Node Name: `http://127.0.0.1:8080/customer/my-designs`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/my-designs
  * Node Name: `http://127.0.0.1:8080/customer/my-designs`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 4 times, the first in likely comment: "<!-- modal-body-fill: the Three.js canvas sizes itself off this box,
                 so it stays a fixed frame rather than a s", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/orders
  * Node Name: `http://127.0.0.1:8080/customer/orders`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/orders
  * Node Name: `http://127.0.0.1:8080/customer/orders`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 5 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/settings
  * Node Name: `http://127.0.0.1:8080/customer/settings`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/settings
  * Node Name: `http://127.0.0.1:8080/customer/settings`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 5 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Merchandise
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Merchandise
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 5 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Finished%2520Goods&search=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (category,search)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Finished%2520Goods&search=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (category,search)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 5 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fsearch=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (search)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/customer/shop%3Fsearch=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (search)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 5 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/notifications
  * Node Name: `http://127.0.0.1:8080/notifications`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `User`
  * Other Info: `The following pattern was used: \bUSER\b and was detected in likely comment: "<!-- User Dropdown -->", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/notifications
  * Node Name: `http://127.0.0.1:8080/notifications`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`
* URL: http://127.0.0.1:8080/verify-code
  * Node Name: `http://127.0.0.1:8080/verify-code`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `from`
  * Other Info: `The following pattern was used: \bFROM\b and was detected 3 times, the first in likely comment: "<!-- ==================================================================
         Global Alert / Confirm Modal

         Replaces", see evidence field for the suspicious comment/snippet.`


Instances: 47

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

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a class="navbar-brand d-flex align-items-center gap-2 text-white" href="#">
            <span class="fw-bold tracking-wider">FAB<span class="text-gradient-gold">LAB</span></span>
        </a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`
* URL: http://127.0.0.1:8080/customer/cart
  * Node Name: `http://127.0.0.1:8080/customer/cart`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a href="#" class="text-white-50 small text-decoration-none" data-notif-mark-all>Mark all read</a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a href="#" class="text-white-50 small text-decoration-none" data-notif-mark-all>Mark all read</a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Merchandise
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: ``
  * Attack: ``
  * Evidence: `<a href="#" class="text-white-50 small text-decoration-none" data-notif-mark-all>Mark all read</a>`
  * Other Info: `Links have been found that do not have traditional href attributes, which is an indication that this is a modern web application.`

Instances: Systemic


### Solution

This is an informational alert and so no changes are required.

### Reference




#### Source ID: 3

### [ Session Management Response Identified ](https://www.zaproxy.org/docs/alerts/10112/)



##### Informational (Medium)

### Description

The given response has been identified as containing a session management token. The 'Other Info' field contains a set of header tokens that can be used in the Header Based Session Management Method. If the request is in a context which has a Session Management Method set to "Auto-Detect" then this rule will change the session management to use the tokens identified.

* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/
  * Node Name: `http://127.0.0.1:8080/`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/cart
  * Node Name: `http://127.0.0.1:8080/customer/cart`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/customize
  * Node Name: `http://127.0.0.1:8080/customer/customize`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/customize%3Fproduct_id=2
  * Node Name: `http://127.0.0.1:8080/customer/customize (product_id)`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/my-designs
  * Node Name: `http://127.0.0.1:8080/customer/my-designs`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders
  * Node Name: `http://127.0.0.1:8080/customer/orders`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/10/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/10/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/2/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/2/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/3/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/3/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/4/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/4/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/5/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/5/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/8/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/8/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/orders/9/receipt
  * Node Name: `http://127.0.0.1:8080/customer/orders/9/receipt`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/settings
  * Node Name: `http://127.0.0.1:8080/customer/settings`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Merchandise
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Finished%2520Goods&search=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (category,search)`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop%3Fsearch=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (search)`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/notifications
  * Node Name: `http://127.0.0.1:8080/notifications`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/verify-code
  * Node Name: `http://127.0.0.1:8080/verify-code`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/cart/checkout
  * Node Name: `http://127.0.0.1:8080/customer/cart/checkout ()(_token)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/profile
  * Node Name: `http://127.0.0.1:8080/customer/profile ()(_method,_token,address,contact_number,degree,email,fullname,gender,photo,section,year)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/settings
  * Node Name: `http://127.0.0.1:8080/customer/settings ()(_method,_token,notifications_enabled)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password ()(_token,email)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/forgot-password/send
  * Node Name: `http://127.0.0.1:8080/forgot-password/send ()(_token,email,verification_mode)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login ()(_token,email,password)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register ()(_token,email,name,password,password_confirmation,phone)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/verify-code
  * Node Name: `http://127.0.0.1:8080/verify-code ()(_token,otp,verification_mode)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend ()(_token,verification_mode)`
  * Method: `POST`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session
cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080
  * Node Name: `http://127.0.0.1:8080`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `XSRF-TOKEN`
  * Other Info: `cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session`
* URL: http://127.0.0.1:8080/customer/shop
  * Node Name: `http://127.0.0.1:8080/customer/shop`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `XSRF-TOKEN`
  * Other Info: `cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Machinery%2520%2526%2520Equipment
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: `laravel-session`
  * Attack: ``
  * Evidence: `laravel-session`
  * Other Info: `cookie:laravel-session`
* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `XSRF-TOKEN`
  * Other Info: `cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login`
  * Method: `GET`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `XSRF-TOKEN`
  * Other Info: `cookie:XSRF-TOKEN`
* URL: http://127.0.0.1:8080/login
  * Node Name: `http://127.0.0.1:8080/login ()(_token,email,password)`
  * Method: `POST`
  * Parameter: `XSRF-TOKEN`
  * Attack: ``
  * Evidence: `XSRF-TOKEN`
  * Other Info: `cookie:XSRF-TOKEN`


Instances: 40

### Solution

This is an informational alert rather than a vulnerability and so there is nothing to fix.

### Reference


* [ https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/ ](https://www.zaproxy.org/docs/desktop/addons/authentication-helper/session-mgmt-id/)



#### Source ID: 3

### [ User Agent Fuzzer ](https://www.zaproxy.org/docs/alerts/10104/)



##### Informational (Medium)

### Description

Check for differences in response based on fuzzed User Agent (eg. mobile sites, access as a Search Engine Crawler). Compares the response statuscode and the hashcode of the response body with the original response.

* URL: http://127.0.0.1:8080/forgot-password
  * Node Name: `http://127.0.0.1:8080/forgot-password ()(_token,email)`
  * Method: `POST`
  * Parameter: `Header User-Agent`
  * Attack: `Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)`
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/register
  * Node Name: `http://127.0.0.1:8080/register ()(_token,email,name,password,password_confirmation,phone)`
  * Method: `POST`
  * Parameter: `Header User-Agent`
  * Attack: `Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)`
  * Evidence: ``
  * Other Info: ``
* URL: http://127.0.0.1:8080/verify-code/resend
  * Node Name: `http://127.0.0.1:8080/verify-code/resend ()(_token,verification_mode)`
  * Method: `POST`
  * Parameter: `Header User-Agent`
  * Attack: `Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)`
  * Evidence: ``
  * Other Info: ``

Instances: Systemic


### Solution



### Reference


* [ https://owasp.org/wstg ](https://owasp.org/wstg)



#### Source ID: 1

### [ User Controllable HTML Element Attribute (Potential XSS) ](https://www.zaproxy.org/docs/alerts/10031/)



##### Informational (Low)

### Description

This check looks at user-supplied input in query string parameters and POST data to identify where certain HTML attribute values might be controlled. This provides hot-spot detection for XSS (cross-site scripting) that will require further review by a security analyst to determine exploitability.

* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Merchandise
  * Node Name: `http://127.0.0.1:8080/customer/shop (category)`
  * Method: `GET`
  * Parameter: `category`
  * Attack: ``
  * Evidence: ``
  * Other Info: `User-controlled HTML attribute values were found. Try injecting special characters to see if XSS might be possible. The page at the following URL:

http://127.0.0.1:8080/customer/shop?category=Merchandise

appears to include user input in:
a(n) [button] tag [data-category] attribute

The user input found was:
category=Merchandise

The user-controlled value was:
merchandise`
* URL: http://127.0.0.1:8080/customer/shop%3Fcategory=Finished%2520Goods&search=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (category,search)`
  * Method: `GET`
  * Parameter: `search`
  * Attack: ``
  * Evidence: ``
  * Other Info: `User-controlled HTML attribute values were found. Try injecting special characters to see if XSS might be possible. The page at the following URL:

http://127.0.0.1:8080/customer/shop?category=Finished%20Goods&search=ZAP

appears to include user input in:
a(n) [input] tag [value] attribute

The user input found was:
search=ZAP

The user-controlled value was:
zap`
* URL: http://127.0.0.1:8080/customer/shop%3Fsearch=ZAP
  * Node Name: `http://127.0.0.1:8080/customer/shop (search)`
  * Method: `GET`
  * Parameter: `search`
  * Attack: ``
  * Evidence: ``
  * Other Info: `User-controlled HTML attribute values were found. Try injecting special characters to see if XSS might be possible. The page at the following URL:

http://127.0.0.1:8080/customer/shop?search=ZAP

appears to include user input in:
a(n) [input] tag [value] attribute

The user input found was:
search=ZAP

The user-controlled value was:
zap`


Instances: 3

### Solution

Validate all input and sanitize output it before writing to any HTML attributes.

### Reference


* [ https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html ](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet.html)


#### CWE Id: [ 20 ](https://cwe.mitre.org/data/definitions/20.html)


#### WASC Id: 20

#### Source ID: 3


