import os
import shutil
from PIL import Image, ImageDraw, ImageFont

brain_dir = "/Users/waibhavmehta/.gemini/antigravity-ide/brain/53166405-6a5c-45ff-b4f0-4c69c7331ae3"
dest_base = "/Applications/XAMPP/xamppfiles/htdocs/School-Demo"

# Find generated files
files = os.listdir(brain_dir)
logo_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("logo_emblem_"))
principal_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("principal_portrait_"))
vice_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("vice_principal_portrait_"))
comp_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("facility_computer_lab_"))
sci_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("facility_science_lab_"))
lib_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("facility_library_"))
sports_file = next(os.path.join(brain_dir, f) for f in files if f.startswith("facility_sports_"))

# Copy images
shutil.copy(logo_file, os.path.join(dest_base, "assets/images/logo.png"))
shutil.copy(principal_file, os.path.join(dest_base, "assets/images/principal.jpg"))
shutil.copy(vice_file, os.path.join(dest_base, "assets/images/vice_principal.jpg"))
shutil.copy(comp_file, os.path.join(dest_base, "assets/images/facilities/computer_lab.jpg"))
shutil.copy(sci_file, os.path.join(dest_base, "assets/images/facilities/science_lab.jpg"))
shutil.copy(lib_file, os.path.join(dest_base, "assets/images/facilities/library.jpg"))
shutil.copy(sports_file, os.path.join(dest_base, "assets/images/facilities/sports.jpg"))

# Icons
logo_img = Image.open(logo_file).convert("RGBA")
icon192 = logo_img.resize((192, 192), Image.Resampling.LANCZOS)
icon192.save(os.path.join(dest_base, "assets/icons/icon-192.png"))

icon512 = logo_img.resize((512, 512), Image.Resampling.LANCZOS)
icon512.save(os.path.join(dest_base, "assets/icons/icon-512.png"))

print("Images and icons setup successfully!")

# Function to create an official document page and save as PDF
def create_pdf_notice(title, ref_no, date_bs, content_lines, filename):
    img = Image.new("RGB", (1240, 1754), color="#ffffff") # A4 at 150dpi
    draw = ImageDraw.Draw(img)
    
    # Header background band
    draw.rectangle([(0, 0), (1240, 220)], fill="#091b33")
    
    # Draw Logo onto header
    l_small = logo_img.resize((160, 160), Image.Resampling.LANCZOS)
    img.paste(l_small, (60, 30), l_small)
    
    # Header Text
    draw.text((250, 40), "INARUWA ENGLISH BOARDING SCHOOL", fill="#ffffff", font_size=42)
    draw.text((250, 95), "Loktantrik Chowk, Inaruwa-1, Sunsari, Koshi Province, Nepal", fill="#d97706", font_size=24)
    draw.text((250, 135), "Phone: +977-25-560124, 560438 | Email: info@iebs.edu.np | Estd: 2039 B.S.", fill="#cbd5e1", font_size=20)
    
    # Gold decorative line
    draw.rectangle([(0, 220), (1240, 228)], fill="#d97706")
    
    # Ref & Date
    draw.text((80, 260), f"Ref No: {ref_no}", fill="#334155", font_size=26)
    draw.text((880, 260), f"Date: {date_bs}", fill="#334155", font_size=26)
    
    # Notice Title Banner
    draw.rectangle([(80, 320), (1160, 390)], fill="#f1f5f9", outline="#cbd5e1", width=2)
    draw.text((120, 335), f"OFFICIAL NOTICE: {title.upper()}", fill="#091b33", font_size=30)
    
    # Body Content
    y = 440
    for line in content_lines:
        if line.startswith("## "):
            y += 20
            draw.text((80, y), line.replace("## ", ""), fill="#091b33", font_size=28)
            y += 45
        elif line.startswith("- "):
            draw.text((110, y), f"•  {line[2:]}", fill="#1e293b", font_size=24)
            y += 38
        else:
            draw.text((80, y), line, fill="#334155", font_size=24)
            y += 38
            
    # Watermark seal in middle
    wm = logo_img.resize((500, 500), Image.Resampling.LANCZOS)
    # Blend watermark softly
    wm_mask = Image.new("L", wm.size, 25)
    img.paste(wm, (370, 700), wm)
    
    # Footer / Signature block
    draw.line([(80, 1550), (400, 1550)], fill="#091b33", width=2)
    draw.text((80, 1560), "Examination Controller", fill="#334155", font_size=24)
    
    draw.line([(840, 1550), (1160, 1550)], fill="#091b33", width=2)
    draw.text((840, 1560), "Principal / Headmaster", fill="#091b33", font_size=24)
    draw.text((840, 1595), "Inaruwa English Boarding School", fill="#64748b", font_size=20)
    
    # Footer bar
    draw.rectangle([(0, 1714), (1240, 1754)], fill="#091b33")
    draw.text((380, 1724), "Official Administrative Circular • Inaruwa English Boarding School", fill="#94a3b8", font_size=18)
    
    out_pdf = os.path.join(dest_base, "uploads/notices", filename)
    img.save(out_pdf, "PDF", resolution=150.0)
    print(f"Generated PDF: {out_pdf}")

# Notice 1: Routine First Term
create_pdf_notice(
    "First Terminal Examination 2083 Routine",
    "IEBS/EXAM/2083-04",
    "2083-04-15 B.S.",
    [
        "This is to notify all students, parents, and guardians that the First Terminal Examination for the",
        "academic session 2083 B.S. is scheduled to commence from Jestha 10, 2083 B.S.",
        "",
        "## Key Guidelines for Examination:",
        "- Admit Cards are mandatory for entering the examination hall.",
        "- Clear all outstanding academic and school transportation dues prior to Jestha 05, 2083.",
        "- Practical examinations for Science and Computer Science will be held from Jestha 05 to Jestha 08.",
        "- Exam Timing: Morning Shift (Grades 6-10): 8:00 AM - 11:00 AM | Day Shift (Nursery - Grade 5): 11:30 AM - 2:00 PM.",
        "",
        "## Subject Schedule Overview:",
        "- Day 1 (Jestha 10): Compulsory English",
        "- Day 2 (Jestha 11): Compulsory Nepali",
        "- Day 3 (Jestha 12): Compulsory Mathematics",
        "- Day 4 (Jestha 13): Science & Technology",
        "- Day 5 (Jestha 14): Social Studies & Human Value Education",
        "- Day 6 (Jestha 15): Computer Science / Optional Mathematics",
        "",
        "We wish all our students the very best for their upcoming examinations."
    ],
    "routine-first-term-2083.pdf"
)

# Notice 2: Dashain Tihar Vacation
create_pdf_notice(
    "Festive Vacation Notice - Dashain & Tihar 2083",
    "IEBS/ADMIN/2083-09",
    "2083-06-25 B.S.",
    [
        "Warm greetings from Inaruwa English Boarding School family on the auspicious occasion of Vijaya Dashami,",
        "Deepawali (Tihar), and Chhath Parva 2083 B.S.",
        "",
        "## Holiday Schedule Announcement:",
        "- School will remain closed from Ashwin 28, 2083 to Kartik 18, 2083 B.S.",
        "- Regular academic classes will resume promptly on Sunday, Kartik 21, 2083 B.S. at 9:00 AM.",
        "- Holiday homework assignments have been distributed to all students by their respective class teachers.",
        "- Administrative office will remain open on select days (Kartik 02 to Kartik 05) for admission inquiries.",
        "",
        "May this festive season bring health, happiness, prosperity, and academic success to all our students and parents."
    ],
    "dashain-tihar-vacation-2083.pdf"
)

# Notice 3: Sports Meet
create_pdf_notice(
    "Annual Inter-House Athletic & Sports Week 2083",
    "IEBS/SPORTS/2083-12",
    "2083-08-01 B.S.",
    [
        "Inaruwa English Boarding School proudly announces the Annual Inter-House Sports Championship 2083 B.S.",
        "",
        "## Event Details & Registration:",
        "- Date: Mangsir 15 to Mangsir 20, 2083 B.S.",
        "- Venue: IEBS Main Sports Ground & Sunsari Stadium Track.",
        "- House Teams: Red Dragons, Blue Panthers, Green Knights, Yellow Tigers.",
        "",
        "## Participating Categories:",
        "- Track Events: 100m, 200m, 400m, 4x100m Relay Sprint.",
        "- Field Events: High Jump, Long Jump, Shot Put.",
        "- Court Sports: Boys & Girls Basketball Tournament, Badminton Singles & Doubles, Table Tennis.",
        "- Primary Section: Marble Race, Spoon Race, Sack Race, Frog Jump.",
        "",
        "All interested house captains and athletes must submit finalized team rosters to Coach Mr. Suresh Thapa",
        "no later than Mangsir 08, 2083 B.S. Family and alumni are cordially invited to witness the Grand Opening Ceremony."
    ],
    "inter-school-sports-meet.pdf"
)

