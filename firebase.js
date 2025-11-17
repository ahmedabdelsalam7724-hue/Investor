// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyA2YTpZvVYRT62B-r_WjIWlxXX4V1AcWrg",
  authDomain: "finance-platform-a0531.firebaseapp.com",
  projectId: "finance-platform-a0531",
  storageBucket: "finance-platform-a0531.firebasestorage.app",
  messagingSenderId: "945606973976",
  appId: "1:945606973976:web:db359053c6030478dd816d",
  measurementId: "G-NJNLW5V3L5"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);