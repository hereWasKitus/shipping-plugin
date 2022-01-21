import React from 'react';
import { Calendar, DateObject } from 'react-multi-date-picker';
import { useDispatch } from 'react-redux';
import { setHolidays } from '../../features/branchesSlice';

function Holidays({ holidays, branchId }) {
  const [dates, setDates] = React.useState(getDatesFromStrings(holidays));
  const months = React.useRef(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'Novermber', 'December']);
  const currentDate = React.useRef(new DateObject('2020/01/01'));
  const dispatch = useDispatch();

  React.useEffect(() => {
    setDates(getDatesFromStrings(holidays));
  }, [holidays]);

  function getFormatedDateStrings(dates) {
    return dates.map(date => date.format('MM/DD/YYYY'));
  }

  function getDatesFromStrings(stringArr) {
    return [...stringArr.map(datestring => new Date(datestring))];
  }

  const handleDateChange = (val) => {
    const formated = getFormatedDateStrings(val);
    dispatch(setHolidays({
      branchId,
      holidays: formated
    }))
  }

  const handleDateRemove = (e, index) => {
    e.preventDefault();
    const newHolidays = dates.filter((datestring, i) => i !== index);
    const formated = newHolidays.map(date => `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`);

    dispatch(setHolidays({
      branchId,
      holidays: formated
    }))
  }

  return (
    <div className="sp-public-holidays-container">
      <Calendar
        multiple
        disableYearPicker={true}
        hideYear
        value={dates}
        onChange={handleDateChange}
        minDate="2020/01/01"
        maxDate="2020/12/31"
        currentDate={currentDate.current}
      />

      <div className="sp-public-holidays">
        {dates.length > 0 && dates.map((date, index) => (
          <div className="sp-public-holiday" key={index}>
            {`${months.current[date.getMonth()]} ${date.getDate()}`}
            <a onClick={e => handleDateRemove(e, index)} href="#" data-date="11/16/2020" className="sp-public-holiday__remove">
              <i className="gg-close"></i>
            </a>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Holidays;
