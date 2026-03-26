# Competency Grid Table Structure

| **Tabelle**                  | **Zweck**                 | **Wichtige Felder (Typ)**                     |
| ---------------------------- | ------------------------- | --------------------------------------------- |
| mdl_competencygrid           | Angelegte Kompetenzraster | id, name, courseid, timecreated, timemodified |
| mdl_competencygrid_skills    | Hauptkompetenzen          | id, gridid (fk), name                         |
| mdl_competencygrid_subskills | Unterkompetenzen          | id, skillid (fk), name                        |
| mdl_competencygrid_levels    | Levelbeschreibungen       | id, subskillid (fk), niveau, description      |

# User Data Persistence

| Tabelle                      | Zweck                                   | Wichtige Felder (Typ)                                                                                           |
| ---------------------------- | --------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| mdl_competencygrid_userinput | Checkbox-Status pro User/Subskill/Level | id, userid (fk), courseid (fk), gridid(fk), skillid (fk), subskillid(fk), levelniveau (fk), timecreated, timemodified |
