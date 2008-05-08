var mooStrength = new Class({

        options: {
        },

        passBox: null,
        passMeter: null,

        initialize: function(box, options) {

            this.passBox = box;
            this.passMeter = null;

            this.setOptions(options);
                
            //generates the div to hold the spell checker controls
            var scoreBarBorder = new Element('div');
            scoreBarBorder.addClass('mooStrengthScoreBarBorder');
            
            var score = new Element('div');
            score.addClass('mooStrengthScore');
            score.setText('Password Strength');
            scoreBarBorder.adopt(score);
            
            var scoreBar = new Element('div');
            scoreBar.addClass('mooStrengthScoreBar');
            scoreBar.innerHTML = "&nbsp;";
            scoreBarBorder.adopt(scoreBar);
                                
            scoreBarBorder.injectAfter(this.passBox);
            
            this.passMeter = scoreBarBorder;

            this.passBox.addEvent('keyup', function (e) {

                var result = this.checkStrength(this.passBox.value);
                var offset = parseInt(result.score * 5);
                if (offset > 375) {
                    offset = 375;
                }
                this.passMeter.getLast().setStyle('backgroundPosition', "-" + offset + "px");
                this.passMeter.getFirst().setText(result.verdict);

            }.bind(this));
        },
        
        checkStrength: function(passwd) {
            
            var intScore = 0;
            var strLog   = "";
            var label    = "";
        
            // PASSWORD LENGTH
            if (passwd.length > 0 && passwd.length < 5) { // length 4 or less
                intScore += 3;
                strLog   = strLog + "3 points for length (" + passwd.length + ")\n";
                
            } else if (passwd.length > 4 && passwd.length < 8) { // length between 5 and 7
                intScore += 6;
                strLog   = strLog + "6 points for length (" + passwd.length + ")\n";
                
            } else if (passwd.length>7 && passwd.length<16) { // length between 8 and 15
                intScore += 15;
                strLog   = strLog + "15 points for length (" + passwd.length + ")\n";
                
            } else if (passwd.length > 15) {     // length 16 or more
                intScore += 20;
                strLog   = strLog + "20 points for length (" + passwd.length + ")\n";
            } 
            
            
            // LETTERS 
            if (passwd.match(/[a-z]/)) {  // [verified] at least one lower case letter
                intScore += 1;
                strLog   = strLog + "1 point for at least one lower case char\n";
            } 
                       
            if (passwd.match(/[A-Z]/)) { // [verified] at least one upper case letter
                intScore += 7;
                strLog   = strLog + "7 points for at least one upper case char\n";
            }
            
            // NUMBERS
            if (passwd.match(/\d+/)) { // [verified] at least one number
                intScore += 10;
                strLog   = strLog + "10 points for at least one number\n";
            }
            
            if (passwd.match(/(.*[0-9].*[0-9].*[0-9])/)) { // [verified] at least three numbers
                intScore += 12;
                strLog   = strLog + "12 points for at least three numbers\n";
            }
            
            
            // SPECIAL CHAR
            if (passwd.match(/.[\.,!,@,#,$,%,^,&,*,?,_,~]/)) { // [verified] at least one special character
                intScore += 15;
                strLog   = strLog + "15 points for at least one special char\n";
            }
            
                                         
            if (passwd.match(/(.*[\.,!,@,#,$,%,^,&,*,?,_,~].*[\.,!,@,#,$,%,^,&,*,?,_,~])/)) { // [verified] at least two special characters 
                intScore += 20;
                strLog   = strLog + "20 points for at least two special chars\n";
            }
        
            
            // COMBOS
            if (passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) { // [verified] both upper and lower case
                intScore += 5;
                strLog   = strLog + "5 combo points for upper and lower letters\n";
            }
    
            if (passwd.match(/([a-zA-Z])/) && passwd.match(/([0-9])/)) { // [verified] both letters and numbers
                intScore += 7;
                strLog   = strLog + "7 combo points for letters and numbers\n";
            }
     
            if (passwd.match(/([a-zA-Z0-9].*[\.,!,@,#,$,%,^,&,*,?,_,~])|([\.,!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)) { // [verified] letters, numbers, and special characters
                intScore += 20;
                strLog   = strLog + "20 combo points for letters, numbers and special chars\n";
            }
        
            if (intScore == 0) {
                strVerdict = "Password Strength";
                label = "noPassword";
            } else if (intScore < 25) {
                strVerdict = "Weak";
                label = "weak";
            } else if (intScore < 40) {
                strVerdict = "Mediocre";
                label = "mediocre";
            } else {
                strVerdict = "Strong";
                label = "strong";
            }
            
            var result = {score: intScore, verdict: strVerdict, log: strLog, cssClass: label};
            
            return result;
        }
});

mooStrength.implement(new Options, new Events);