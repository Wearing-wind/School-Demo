import os
import shutil
from PIL import Image

brain_dir = "/Users/waibhavmehta/.gemini/antigravity-ide/brain/53166405-6a5c-45ff-b4f0-4c69c7331ae3"
dest_base = "/Applications/XAMPP/xamppfiles/htdocs/School-Demo"

files = os.listdir(brain_dir)

# Helper to find latest file matching prefix
def get_file(prefix):
    matches = [os.path.join(brain_dir, f) for f in files if f.startswith(prefix)]
    return sorted(matches)[-1] if matches else None

logo_f = get_file("apex_logo_emblem_") or get_file("logo_emblem_")
principal_f = get_file("apex_principal_portrait_") or get_file("principal_portrait_")
director_f = get_file("apex_director_portrait_") or get_file("vice_principal_portrait_")
robotics_f = get_file("apex_facility_robotics_") or get_file("facility_computer_lab_")
bus_f = get_file("apex_facility_bus_") or get_file("facility_sports_")
science_fair_f = get_file("apex_event_science_fair_") or get_file("facility_science_lab_")

comp_f = get_file("facility_computer_lab_") or robotics_f
sci_f = get_file("facility_science_lab_") or science_fair_f
lib_f = get_file("facility_library_") or robotics_f
sports_f = get_file("facility_sports_") or bus_f

# Copy core images
if logo_f:
    shutil.copy(logo_f, os.path.join(dest_base, "assets/images/logo.png"))
    logo_img = Image.open(logo_f).convert("RGBA")
    logo_img.resize((192, 192), Image.Resampling.LANCZOS).save(os.path.join(dest_base, "assets/icons/icon-192.png"))
    logo_img.resize((512, 512), Image.Resampling.LANCZOS).save(os.path.join(dest_base, "assets/icons/icon-512.png"))

if principal_f:
    shutil.copy(principal_f, os.path.join(dest_base, "assets/images/principal.jpg"))
if director_f:
    shutil.copy(director_f, os.path.join(dest_base, "assets/images/director.jpg"))

# Copy Facilities
if robotics_f:
    shutil.copy(robotics_f, os.path.join(dest_base, "assets/images/facilities/robotics_lab.jpg"))
if comp_f:
    shutil.copy(comp_f, os.path.join(dest_base, "assets/images/facilities/computer_lab.jpg"))
if lib_f:
    shutil.copy(lib_f, os.path.join(dest_base, "assets/images/facilities/library.jpg"))
if sports_f:
    shutil.copy(sports_f, os.path.join(dest_base, "assets/images/facilities/sports_ground.jpg"))
if bus_f:
    shutil.copy(bus_f, os.path.join(dest_base, "assets/images/facilities/school_bus.jpg"))

# Copy Events
if science_fair_f:
    shutil.copy(science_fair_f, os.path.join(dest_base, "assets/images/events/science_fair.jpg"))
if sports_f:
    shutil.copy(sports_f, os.path.join(dest_base, "assets/images/events/sports_day.jpg"))
if sci_f:
    shutil.copy(sci_f, os.path.join(dest_base, "assets/images/events/annual_function.jpg"))
if robotics_f:
    shutil.copy(robotics_f, os.path.join(dest_base, "assets/images/events/art_exhibition.jpg"))
if lib_f:
    shutil.copy(lib_f, os.path.join(dest_base, "assets/images/events/debate.jpg"))
if comp_f:
    shutil.copy(comp_f, os.path.join(dest_base, "assets/images/events/assembly.jpg"))

print("Apex Model Secondary School assets populated successfully!")
