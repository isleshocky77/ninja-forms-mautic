# About #

This is _ninja-forms-mautic_. A [Wordpress] plugin and [Ninja Forms][ninja-forms] add-on which 
allows you to save form data to [Mautic][mautic]. 

## Configuration ##

1. Enable API on Mautic
2. Create API Credentials
3. Install and activate _ninja-forms-mautic_
4. Navigate to Forms > Settings
5. Under **Mautic Settings** fill in authentication information and click "Authorize"

## Usage ##

* Navigate to editing a form
* Go to **Emails & Actions** and add "Send to Mautic"
* Configure the action by adding _Field Mappings_
  * The left side should be the _admin key_ for the ninja form
  * The right side should be the API field name of Mautic (e.g. firstname, lastname, email, phone, company)

## License ##

    ninja-forms-mautic is licensed under GPLv3.

    ninja-forms-mautic is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    ninja-forms-mautic is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with ninja-forms-mautic.  If not, see <http://www.gnu.org/licenses/>.

[mautic]: https://www.mautic.org/
[ninja-forms]: https://ninjaforms.com/
[wordpress]: https://wordpress.com/
