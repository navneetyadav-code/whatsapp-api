import mysql from '../worker/node_modules/mysql2/promise.js';
import fs from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const envPath = resolve(__dirname, '..', '.env');
const env = fs.readFileSync(envPath, 'utf8');

const dbUser = env.match(/DB_USER=(.*)/)?.[1]?.trim() || 'root';
const dbPass = env.match(/DB_PASSWORD=(.*)/)?.[1]?.trim() || '';
const dbName = env.match(/DB_NAME=(.*)/)?.[1]?.trim() || 'whatsapp_api';
const dbHost = env.match(/DB_HOST=(.*)/)?.[1]?.trim() || '127.0.0.1';
const dbPort = parseInt(env.match(/DB_PORT=(.*)/)?.[1]?.trim() || '3306', 10);

const coachingChunks = [
  {
    title: 'About Aadi Shree Classes & Institute Profile',
    content: `Aadi Shree Classes is a premier coaching institute dedicated to academic excellence in Board Exams (Classes 9th to 12th) and Competitive Entrance Exams (IIT-JEE Main & Advanced, NEET-UG, NDA, and CUET). With top faculties, small batches (max 25-30 students), and a proven track record of 95%+ success rate, we focus on deep conceptual clarity, daily problem solving, and personalized student mentoring.`
  },
  {
    title: 'Courses Offered & Target Exams',
    content: `Aadi Shree Classes offers comprehensive coaching for:
1. Foundation Batches: Classes 9th & 10th (CBSE, ICSE, and State Boards) for Maths, Science, and Social Studies.
2. Senior Secondary Batches: Classes 11th & 12th Science Stream (Physics, Chemistry, Mathematics, Biology).
3. Commerce Stream: Classes 11th & 12th (Accountancy, Economics, Business Studies, Applied Maths).
4. Competitive Entrance: IIT-JEE (Main + Advanced), NEET-UG (Medical), NDA (National Defence Academy), and CUET.`
  },
  {
    title: 'Batch Timings & Class Schedule',
    content: `Classes are conducted 6 days a week (Monday to Saturday):
• Morning Batches (Droppers & Early Birds): 7:00 AM to 10:30 AM.
• Evening Batches (School-Going Students): 4:00 PM to 7:30 PM.
• Weekend Batches (Special Revision & Tests): Saturday & Sunday for competitive problem-solving sessions.
Each subject lecture is 1 hour 15 minutes followed by a 15-minute doubt resolution window.`
  },
  {
    title: 'Fee Structure & Payment Plans',
    content: `Official Fee Structure for Academic Sessions:
• Class 9th & 10th (All Subjects): ₹18,000 per year (or ₹1,800 monthly).
• Class 11th & 12th Board Exam (PCM/PCB): ₹28,000 per year.
• Class 11th & 12th Commerce: ₹24,000 per year.
• 1-Year Comprehensive IIT-JEE / NEET Target Batch: ₹45,000 per year.
• 2-Year Integrated Program (Class 11+12 + JEE/NEET): ₹75,000 total.
Fees can be paid via UPI (Google Pay, PhonePe, Paytm), Netbanking, Credit/Debit card, or Cash at the admission counter. Installment options are available in 2 or 3 easy installments.`
  },
  {
    title: 'ASAT Scholarship & Fee Concessions',
    content: `Aadi Shree Classes conducts the ASAT (Aadi Shree Scholarship Admission Test) every month:
• Rank 1 to 5: Up to 90% tuition fee waiver.
• Rank 6 to 20: 50% tuition fee waiver.
• Rank 21 to 50: 25% tuition fee waiver.
Special Concessions: 10% discount for real siblings enrolled together, and a 15% merit concession for students scoring 90%+ in their previous school board exams.`
  },
  {
    title: 'Free 2-Day Demo Classes Policy',
    content: `We offer a 2-Day Free Trial Demo Class for any course before admission:
• Students can attend 2 full days of live classes in their respective stream.
• Experience the teaching methodology, faculty interaction, and study material.
• No registration fee or payment is required for demo attendance. Book demo by messaging student name, class, and stream on our WhatsApp.`
  },
  {
    title: 'Faculty Profile & Teaching Staff',
    content: `Our teaching faculty members are seasoned educators with over 8 to 15 years of teaching experience:
• Physics Faculty: Ex-IIT Kanpur alumnus with 10+ years coaching JEE aspirants.
• Chemistry Faculty: Ph.D. in Organic Chemistry with national-level author credentials.
• Mathematics Faculty: Senior Board Examiner known for high-speed shortcut tricks and calculus mastery.
• Biology Faculty: M.Sc. Gold Medalist with proven records of 340+ scores in NEET Biology.`
  },
  {
    title: 'Batch Size & Personal Mentorship',
    content: `Unlike crowded coaching institutes with 100+ students in a hall, Aadi Shree Classes maintains a strict cap of 25 to 30 students per batch. This guarantees:
• Every student gets individual eye contact and attention from the teacher.
• Teachers track each student's notebook, homework submission, and weak topics individually.`
  },
  {
    title: 'Daily Doubt-Clearing Helpdesk',
    content: `Dedicated 1-on-1 Doubt Sessions are available every day:
• Offline Doubt Counter: Open daily from 3:00 PM to 4:00 PM before evening classes begin.
• Post-Class Doubt Window: 15 minutes after every lecture.
• 24/7 WhatsApp Doubt Group: Monitored by subject faculties where students can send photo of difficult questions and receive step-by-step video/text solutions within a few hours.`
  },
  {
    title: 'Study Material, Modules & Question Banks',
    content: `Every enrolled student receives complete Aadi Shree Printed Study Material package:
1. Theory Modules with concept maps and formula cheat-sheets.
2. Daily Practice Problems (DPP) sets with 20 questions per day.
3. Level-wise Question Banks: Level 1 (School Boards), Level 2 (JEE Main/NEET), Level 3 (JEE Advanced).
4. Past 10 Years Chapter-wise Solved Question Papers (PYQs) with model answers.`
  },
  {
    title: 'Weekly Sunday Tests & OMR Assessment',
    content: `Continuous assessment is the core of our training:
• Every Sunday: Scheduled chapter test (Subjective for Boards, OMR-based for JEE/NEET).
• Automated Scorecards: Within 24 hours of test completion, detailed marks, class rank, percentile, and answer keys are sent directly to parents on WhatsApp.
• Cumulative Monthly Tests: Testing the entire month's syllabus to prevent forgetting.`
  },
  {
    title: 'Parent-Teacher Meetings (PTM) & Progress Tracking',
    content: `Parent-Teacher Meetings (PTM) are held on the 1st Sunday of every month:
• Discussion with subject teachers on student marks, test performance, and homework consistency.
• Online Zoom PTM option is available for outstation or working parents unable to visit the center in person.`
  },
  {
    title: 'Biometric Attendance & Parent WhatsApp Alerts',
    content: `Student safety and discipline are automated:
• Every student checks in with a biometric fingerprint / RFID card at the institute entrance.
• Automated SMS/WhatsApp alert is dispatched to parents as soon as the student enters and leaves the institute premises.
• If a student is absent without prior leave application, parents receive an immediate follow-up call by 11:00 AM.`
  },
  {
    title: 'Air-Conditioned Self-Study Library',
    content: `Aadi Shree Classes provides an in-house modern self-study library:
• Open Monday to Saturday: 8:00 AM to 8:00 PM; Sunday: 9:00 AM to 4:00 PM.
• High-speed Wi-Fi, individual charging sockets, peaceful air-conditioned cabins, and 500+ reference books available for free for all enrolled students.`
  },
  {
    title: 'Board Exam Booster & Crash Courses',
    content: `Short-term intensive programs:
• 60-Day Board Exam Booster (Starts Nov 15 every year): Complete NCERT line-by-line revision, sample paper solving, and answer presentation techniques for Class 10 & 12.
• 45-Day JEE / NEET Rapid Revision Crash Course (Starts Jan & April): 200+ high-yield problem solving, full mock test series, and negative marking elimination strategies.`
  },
  {
    title: 'Recorded Video Lectures & Backup App',
    content: `Missed a lecture due to fever or school exams?
• Every live classroom session is recorded in HD quality and uploaded to the Aadi Shree Student Mobile App.
• Enrolled students get free app login with 24/7 access to review missed lectures, download digital notes, and re-watch complex topic explanations anytime.`
  },
  {
    title: 'Hostel & Outstation Students Accommodation',
    content: `For students relocating from other cities/towns:
• Aadi Shree Classes has verified tie-ups with clean, secure, separate boys and girls hostels within 200 to 500 meters of the coaching center.
• Hostels provide 3 nutritious meals daily, RO drinking water, 24/7 security guard, and power backup. Monthly hostel charges range between ₹6,000 to ₹8,500 including food.`
  },
  {
    title: '100% Fee Refund & Cancellation Policy',
    content: `Transparent refund policy:
• If a student or parent decides to discontinue within 7 days of the first official class, 100% of the tuition fee is refunded back (only ₹500 administrative registration fee is deducted).
• Refund is processed directly to bank account within 3 working days without unnecessary questions.`
  },
  {
    title: 'Admission Procedure & Required Documents',
    content: `How to register for admission at Aadi Shree Classes:
1. Walk into our center reception or register online on our portal.
2. Fill the Student Enrollment Form.
3. Submit 2 passport-size photographs, copy of previous year school mark-sheet, and student Aadhaar Card photocopy.
4. Select payment plan (Full one-time or installment) and collect student ID card, uniform/bag, and study module pack.`
  },
  {
    title: 'Medium of Instruction (Language of Teaching)',
    content: `Classes are taught in comfortable bilingual format (Hinglish):
• Teachers explain difficult concepts in clear, easy Hindi and English so every student grasps the fundamental logic.
• All printed notes, question banks, and board assignments are provided in English (Hindi medium study material is also available upon special request for UP/Bihar state board batches).`
  },
  {
    title: 'Computer-Based Test (CBT) Portal for JEE & NEET',
    content: `Online Test Portal:
• Real-time NTA exam simulation portal matching the exact user interface of IIT-JEE and NEET.
• Comprehensive post-test AI analytics showing time spent per question, accuracy rate, negative marks breakdown, and all-India percentile ranking against other students.`
  },
  {
    title: 'Board Practical Exams & Laboratory Workshops',
    content: `Special practical sessions:
• Before school Class 10 & 12 CBSE board practical examinations, we conduct hands-on laboratory workshops for Physics, Chemistry, and Biology.
• Students perform core experiments, practice viva-voce questions, and verify practical record files with experienced school evaluators.`
  },
  {
    title: 'Discipline, Classroom Rules & Mobile Policy',
    content: `Strict classroom environment:
• Mobile phones must be kept on silent or deposited in safe classroom lockers during lecture hours.
• Minimum 80% attendance is required to be eligible for institute scholarship continuation.
• Ragging, bullying, or disrespectful behavior results in immediate termination of admission.`
  },
  {
    title: 'Center Address, Landmark & Visiting Hours',
    content: `Aadi Shree Classes Main Campus:
• Address: Plot No. 14, Main Market Road, Near City Metro Station Gate 2, New Delhi / Regional Center.
• Visiting & Counseling Hours: Monday to Saturday from 8:00 AM to 8:00 PM; Sunday from 9:00 AM to 2:00 PM.
• Parents and students are always welcome to walk in for free career counseling and campus tour.`
  },
  {
    title: 'Helpline Contact Numbers & Support Desk',
    content: `Official Helpdesk & Communication Channels:
• WhatsApp & Calling Admission Helpline: +91 85810 65107 / +91 95072 86092.
• Accounts & Fee Inquiries Desk: Available Monday to Saturday 10:00 AM to 6:00 PM.
• Email Support: info@aadishreeclasses.com / admissions@aadishreeclasses.com.`
  }
];

async function seedCoachingKnowledge() {
  console.log('=====================================================');
  console.log(' SEEDING 25 COACHING KNOWLEDGE BASE RECORDS (RAG)    ');
  console.log('=====================================================\n');

  const db = await mysql.createConnection({
    host: dbHost,
    user: dbUser,
    password: dbPass,
    database: dbName,
    port: dbPort
  });

  const userId = 1;

  // Clear any existing dummy chunks for user 1
  await db.query('DELETE FROM ai_knowledge_chunks WHERE user_id = ?', [userId]);

  let inserted = 0;
  for (const item of coachingChunks) {
    await db.execute(
      `INSERT INTO ai_knowledge_chunks (user_id, title, content, embedding_model, is_active)
       VALUES (?, ?, ?, 'local-tfidf', 1)`,
      [userId, item.title, item.content]
    );
    inserted++;
    console.log(`✔ [${inserted}/25] Inserted: "${item.title}"`);
  }

  const [countRes] = await db.query('SELECT COUNT(*) as total FROM ai_knowledge_chunks WHERE user_id = ?', [userId]);
  console.log(`\n🎉 Total Knowledge Chunks stored for User 1: ${countRes[0]?.total}`);

  await db.end();
}

seedCoachingKnowledge().catch(err => {
  console.error('Seeding failed:', err);
  process.exit(1);
});
