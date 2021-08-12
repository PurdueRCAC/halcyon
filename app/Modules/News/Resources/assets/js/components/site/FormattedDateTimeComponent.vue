<template>
    <span>Posted by Person on {{ formattedDateTime }}</span>
</template>

<script>
    export default {
        props: ['rawDateTime'],
        computed: {
            formattedDateTime() {
                const rawDate = this.rawDateTime.substring(0, this.rawDateTime.indexOf("T"));
				const rawTime = this.rawDateTime.substring(this.rawDateTime.indexOf("T") + 1, this.rawDateTime.indexOf("Z"));

				const date_list = rawDate.split('-');
				let month_word = null;
				switch (parseInt(date_list[1])) {
					case 1:
						month_word = "January";
						break;
					case 2:
						month_word = "February";
						break;
					case 3:
						month_word = "March";
						break;
					case 4:
						month_word = "April";
						break;
					case 5:
						month_word = "May";
						break;
					case 6:
						month_word = "June";
						break;
					case 7:
						month_word = "July";
						break;
					case 8:
						month_word = "August";
						break;
					case 9:
						month_word = "September";
						break;
					case 10:
						month_word = "October";
						break;
					case 11:
						month_word = "November";
						break;
					case 12:
						month_word = "December";
						break;
				}
				const formattedDate = month_word + " " + parseInt(date_list[2]).toString() + ", " + date_list[0].toString();

				const time_list = rawTime.split(':');
				const amOrPm = (parseInt(time_list[0]) / 12) < 1 ? "am" : "pm"
				const hour = (parseInt(time_list[0]) % 12) > 0 ? (parseInt(time_list[0]) % 12) : 12;
				const minute_str = time_list[1];
				const formattedTime = hour.toString() + ":" + minute_str + amOrPm + " EDT";

				return formattedDate + " " + formattedTime;
            }
        }
    }
</script>