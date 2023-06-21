export default {
    loadingScreenShared(show) {
        let loader = document.getElementById('loader');
        if (typeof loader !== 'undefined' && loader !== null) {
            if (show == 'true') {
                document.getElementById('loader').style.display = 'block';
            } else {
                document.getElementById('loader').style.display = 'none';
            }
        }
    },
    loadingScreenStartShared() {
        this.loadingScreen('false');
    },
    getTodayDateShared() {
        const date = new Date();

        let day = date.getDate();
        let month = date.getMonth() + 1;
        let year = date.getFullYear();
        if (month < 10) {
            month = '0' + month
        }

        this.currentDate = year + '-' + month + '-' + day;
    },
    getActualTimeShared() {
        let date = new Date();
        let hour = date.getHours();
        if (hour < 10) {
            hour = '0' + hour;
        }
        let minute = date.getMinutes();
        if (minute < 10) {
            minute = '0' + minute;
        }
        let second = date.getSeconds();
        if (second < 10) {
            second = '0' + second;
        }

        this.actualTime = hour + ':' + minute + ':' + second;
    }
}