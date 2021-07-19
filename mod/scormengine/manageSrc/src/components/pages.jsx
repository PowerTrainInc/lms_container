import React, { Component, useState, useEffect } from "react";
const genPagination = function(current, total, perpage) {
    let localCurrent = current;
    localCurrent -= 1;
    const totalPages = Math.ceil(total / perpage);
    const pageConfig = {};
    if (localCurrent > -1) {
        pageConfig.prev = {
            page: localCurrent,
        };
    }
    if (localCurrent < totalPages - 2) {
        pageConfig.next = {
            page: localCurrent + 2,
        };
    }

    if (localCurrent > 3) {
        pageConfig.start = {
            page: 0,
        };
    }
    if (totalPages - (localCurrent + 2) > 3) {
        pageConfig.end = {
            page: totalPages - 1,
        };
    }
    pageConfig.pages = [];
    let start = 0;
    if (localCurrent > 3) {
        start = localCurrent - 2;
    }
    for (let i = start; i < Math.min(start + 7, totalPages); i++) {
        pageConfig.pages.push({
            page: i,
            display: i + 1,
            current: i === localCurrent + 1,
        });
    }

    return pageConfig;
};

export default class Pageinator extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            currentPage: 0,
        };
        this.selectPage = this.selectPage.bind(this);
    }
    renderPages(pageconfig) {
        const pages = [];
        for (const i in pageconfig.pages) {
            pages.push(<li  key={pageconfig.pages[i].page} className={"page-item " + (pageconfig.pages[i].current ? 'active' : '')} ><a class="page-link" onClick={() => { this.selectPage(pageconfig.pages[i].page); }}> {pageconfig.pages[i].display}</a></li>);
        }
        return pages;
    }
    genStyle(cond) {
        return { 'pointer-events': !cond ? 'none':'all', color: !cond ? 'gray':'unset' };
    }
    selectPage(currentPage) {
        if (this.props.onPage)
            {this.props.onPage(currentPage);}
        this.setState(() => ({currentPage}));
        return false;
    }
    render() {
        const pageconfig = genPagination(this.props.currentPage, this.props.total, this.props.perPage);
        console.log(pageconfig);
        return (<nav style={{"textAlign": "center"}}>
          <ul style={{display: "flex",
    justifyContent: "center"}} className="pagination">
             
            {pageconfig.start && 
            <li  className="page-item">
              <a className="page-link"   onClick={() => {this.selectPage(  pageconfig.start.page )}} aria-label="Previous">
                <span  aria-hidden="true">&laquo;&laquo;</span>
              </a> 
            </li>}
            
            {pageconfig.prev && 
            <li className="page-item">
              <a className="page-link"  style={this.genStyle(pageconfig.prev)}  onClick={() => {this.selectPage(  pageconfig.prev.page )}} aria-label="Previous">
                <span style={this.genStyle(pageconfig.prev)} aria-hidden="true">&laquo;</span>
              </a> 
            </li>
            }
            {this.renderPages(pageconfig)}
            
            {pageconfig.next && 
            <li className="page-item">
              <a className="page-link" style={this.genStyle(pageconfig.next)} onClick={() => {this.selectPage(  pageconfig.next.page )}} aria-label="Next">
                <span style={this.genStyle(pageconfig.next)}  aria-hidden="true">&raquo;</span>
              </a>
            </li>}
            
            {pageconfig.end &&  
            <li className="page-item">
              <a className="page-link" onClick={() => {this.selectPage( pageconfig.end.page)}} aria-label="Previous">
                <span  aria-hidden="true">&raquo;&raquo;</span>
              </a> 
            </li>
            }
            
          </ul>
        </nav>);
    }
}
;