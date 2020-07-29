/**
 * replace <'s and '>
 *
 * @param   {string}  text
 * @return  {string}
 */
function PrepareText(text) {
	text = text.replace(/</g, '&lt;');
	text = text.replace(/>/g, '&gt;');

	return text;
}

/**
 * Used to highlight stemmed keyword matches in news text
 *
 * @param   {string}  text
 * @return  {string}
 */
function HighlightMatches(text) {
	var search = document.getElementById("keywords").value;
	if (search.replace(' ','') == "") {
		return text;
	}

	// Filter out any bad characters
	search = search.replace(/[^a-zA-Z0-9_ ]/g, '');
	var keywords = search.split(/ /);

	for (var i = 0; i < keywords.length; i++) {
		keywords[i] = stemmer(keywords[i]).toLowerCase();
	}

	// amethyst, sky, green, honeydew, jade, lime, mallow, orpiment, 
	// pink, red, blue, turquoise, uranium, wine, yellow
	var colors = [
		'rgb(240,163,255)','rgb(94,241,242)','rgb(43,206,72)','rgb(255,204,153)',
		'rgb(148,255,181)','rgb(157,204,0)','rgb(194,0,136)','rgb(255,164,5)','rgb(255,168,187)',
		'rgb(255,0,16)','rgb(0,117,220)','rgb(0,153,143)','rgb(224,255,102)','rgb(153,0,0)','rgb(255,225,0)'
	];

	var regx = new RegExp(/(<[^>]+>)|((^|\b)([^<]+?)(\b|$))/i);
	var m;
	var prev = -1;
	var txt = "";
	var temp = "";
	var keyid = 0;
	var lastMatch = 0;
	var color = "";
	// iterate through matches
	while (m = regx.exec(text)) {
		txt = m[0];
		keyid = keywords.indexOf(stemmer(txt).toLowerCase());
		if (keyid != -1) {
			// if number of keywords exceeds color array, loop back around
			color = colors[keyid % colors.length];
			// include everything that was skipped and the match
			temp += text.substr(0, m.index) + "<span style='background-color:" + color + "'>" + txt + "</span>";
		} else {
			temp += text.substr(0, m.index) + txt
		}
		text = text.substr(m.index + m[0].length);
	}
	temp += text;

	return temp;
}

// Porter stemmer in Javascript. Few comments, but it's easy to follow against the rules in the original
// paper, in
//
//  Porter, 1980, An algorithm for suffix stripping, Program, Vol. 14,
//  no. 3, pp 130-137,
//
// see also http://www.tartarus.org/~martin/PorterStemmer

// Release 1 be 'andargor', Jul 2004
// Release 2 (substantially revised) by Christopher McKenzie, Aug 2009

var stemmer = (function(){
	var step2list = {
		"ational" : "ate",
		"tional" : "tion",
		"enci" : "ence",
		"anci" : "ance",
		"izer" : "ize",
		"bli" : "ble",
		"alli" : "al",
		"entli" : "ent",
		"eli" : "e",
		"ousli" : "ous",
		"ization" : "ize",
		"ation" : "ate",
		"ator" : "ate",
		"alism" : "al",
		"iveness" : "ive",
		"fulness" : "ful",
		"ousness" : "ous",
		"aliti" : "al",
		"iviti" : "ive",
		"biliti" : "ble",
		"logi" : "log"
	},

	step3list = {
		"icate" : "ic",
		"ative" : "",
		"alize" : "al",
		"iciti" : "ic",
		"ical" : "ic",
		"ful" : "",
		"ness" : ""
	},

	c = "[^aeiou]",          // consonant
	v = "[aeiouy]",          // vowel
	C = c + "[^aeiouy]*",    // consonant sequence
	V = v + "[aeiou]*",      // vowel sequence

	mgr0 = "^(" + C + ")?" + V + C,               // [C]VC... is m>0
	meq1 = "^(" + C + ")?" + V + C + "(" + V + ")?$",  // [C]VC[V] is m=1
	mgr1 = "^(" + C + ")?" + V + C + V + C,       // [C]VCVC... is m>1
	s_v = "^(" + C + ")?" + v;                   // vowel in stem

	return function (w) {
		var stem,
			suffix,
			firstch,
			re,
			re2,
			re3,
			re4,
			origword = w;

		if (w.length < 3) {
			return w;
		}

		w = w.toLowerCase();

		firstch = w.substr(0,1);
		if (firstch == "y") {
			w = firstch.toUpperCase() + w.substr(1);
		}

		// Step 1a
		re = /^(.+?)(ss|i)es$/;
		re2 = /^(.+?)([^s])s$/;

		if (re.test(w)) {
			w = w.replace(re,"$1$2");
		}
		else if (re2.test(w)) {
			w = w.replace(re2,"$1$2");
		}

		// Step 1b
		re = /^(.+?)eed$/;
		re2 = /^(.+?)(ed|ing)$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			re = new RegExp(mgr0);
			if (re.test(fp[1])) {
				re = /.$/;
				w = w.replace(re,"");
			}
		} else if (re2.test(w)) {
			var fp = re2.exec(w);
			stem = fp[1];
			re2 = new RegExp(s_v);
			if (re2.test(stem)) {
				w = stem;
				re2 = /(at|bl|iz)$/;
				re3 = new RegExp("([^aeiouylsz])\\1$");
				re4 = new RegExp("^" + C + v + "[^aeiouwxy]$");
				if (re2.test(w)) {
					w = w + "e";
				}
				else if (re3.test(w)) {
					re = /.$/;
					w = w.replace(re,"");
				}
				else if (re4.test(w)) {
					w = w + "e";
				}
			}
		}

		// Step 1c
		re = /^(.+?)y$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(s_v);
			if (re.test(stem)) {
				w = stem + "i";
			}
		}

		// Step 2
		re = /^(.+?)(ational|tional|enci|anci|izer|bli|alli|entli|eli|ousli|ization|ation|ator|alism|iveness|fulness|ousness|aliti|iviti|biliti|logi)$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			stem = fp[1];
			suffix = fp[2];
			re = new RegExp(mgr0);
			if (re.test(stem)) {
				w = stem + step2list[suffix];
			}
		}

		// Step 3
		re = /^(.+?)(icate|ative|alize|iciti|ical|ful|ness)$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			stem = fp[1];
			suffix = fp[2];
			re = new RegExp(mgr0);
			if (re.test(stem)) {
				w = stem + step3list[suffix];
			}
		}

		// Step 4
		re = /^(.+?)(al|ance|ence|er|ic|able|ible|ant|ement|ment|ent|ou|ism|ate|iti|ous|ive|ize)$/;
		re2 = /^(.+?)(s|t)(ion)$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(mgr1);
			if (re.test(stem)) {
				w = stem;
			}
		} else if (re2.test(w)) {
			var fp = re2.exec(w);
			stem = fp[1] + fp[2];
			re2 = new RegExp(mgr1);
			if (re2.test(stem)) {
				w = stem;
			}
		}

		// Step 5
		re = /^(.+?)e$/;
		if (re.test(w)) {
			var fp = re.exec(w);
			stem = fp[1];
			re = new RegExp(mgr1);
			re2 = new RegExp(meq1);
			re3 = new RegExp("^" + C + v + "[^aeiouwxy]$");
			if (re.test(stem) || (re2.test(stem) && !(re3.test(stem)))) {
				w = stem;
			}
		}

		re = /ll$/;
		re2 = new RegExp(mgr1);
		if (re.test(w) && re2.test(w)) {
			re = /.$/;
			w = w.replace(re,"");
		}

		// and turn initial Y back to y

		if (firstch == "y") {
			w = firstch.toLowerCase() + w.substr(1);
		}

		return w;
	}
})();
